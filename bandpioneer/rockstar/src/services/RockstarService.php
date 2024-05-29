<?php
/**
 * Band Pioneer Craft CMS 4.x
 *
 * Band Pioneer plugin for base website design properties.
 *
 * @link      https://bandpioneer.com
 * @copyright Copyright (c) 2023 Band Pioneer
 */

namespace bandpioneer\rockstar\services;

use Craft;
use craft\gql\base\ObjectType;
use yii\base\Component;

use craft\helpers\ElementHelper;
use craft\helpers\DateTimeHelper;
use craft\helpers\StringHelper;
use craft\helpers\Db;
use craft\helpers\Image;
use craft\helpers\Assets as AssetsHelper;

use craft\elements\Entry;
use craft\elements\Asset;
use craft\errors\ImageException;

use bandpioneer\rockstar\Rockstar;
use bandpioneer\rockstar\records\BandRecord as BandRecord;
use bandpioneer\rockstar\records\RankingRecord as RankingRecord;
use bandpioneer\rockstar\records\EpkRecord as EpkRecord;
use bandpioneer\rockstar\models\EpkModel as EpkModel;

/**
 * @author    Band Pioneer
 * @package   Rockstar
 */
class RockstarService extends Component
{
    const MAX_IMAGE_SIZE = 500000; // (0.5 MB) in bytes
    const IMAGE_VOLUME_HANDLE = 'rockstarAssets';

    /*** PRIVATE MEMBERS ***/

    private function getUserVolumePath($user)
    {
        return "user-$user->id/images";
    }

    private function getYoutubeIdFromUrl($url)
    {
        $parts = parse_url($url);
        if(isset($parts['query']))
        {
            parse_str($parts['query'], $qs);
            if(isset($qs['v']))
            {
                return $qs['v'];
            }
            else if(isset($qs['vi']))
            {
                return $qs['vi'];
            }
        }
        if(isset($parts['path']))
        {
            $path = explode('/', trim($parts['path'], '/'));
            return $path[count($path)-1];
        }
        return false;
    }

    private function updateCurrentUserEpkProperty($propName, $propVal)
    {
        $transaction = Craft::$app->getDb()->beginTransaction();

        try 
        {
            $currentUser = Craft::$app->getUser()->getIdentity();
            $now = Db::prepareDateForDb(DateTimeHelper::now());
            $epkRecord = EpkRecord::findOne(['userId' => $currentUser->id]) ?? new EpkRecord();

            if($epkRecord->getIsNewRecord())
            {
                $epkRecord->dateCreated = $now;
                $epkRecord->userId = $currentUser->id;
            }
            $epkRecord->dateUpdated = $now;

            $epkRecord->{$propName} = $propVal;
            $epkRecord->save();

            $transaction->commit();
        }
        catch (Throwable $e)
        {
            $transaction->rollBack();
            throw $e;
            return false;
        }
    }

    private function cleanTitle($title)
    {
        return str_replace( array( '\'', '"', ',' , ';', '<', '>' ), ' ', $title);
    }

    private function extractIframe($html)
    {
        // remove all script tags
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $html);

        // remove all iframe tags that don't have a valid source
        $html = preg_replace('/<iframe\b([^>]*) src="((https?):\/\/(?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,})([^"]*)"([^>]*)>(.*?)<\/iframe>/is', '<iframe$1 src="$2$4"$5></iframe>', $html);

        // remove any other HTML tags except for iframe and embed
        // $html = strip_tags($html, '<iframe><embed>');
        $allowed_tags = '<iframe><iframe scrolling><iframe width><iframe frameborder><iframe src><iframe allow>';
        $html = strip_tags($html, $allowed_tags);

        // in case anything else remaining, return iframe only
        $start = strpos($html, '<iframe');
        $end = strpos($html, '</iframe>');
        if ($start === false || $end === false)
        {
            return null;
        }

        return substr($html, $start, $end - $start + 9); // +9 to include closing </iframe> tag
    }

    /*** PUBLIC MEMBERS ***/

    public function getUserOrderedItemsForEntry($entryId)
    {
        $rankingRecords = RankingRecord::find()->where(['entryId' => $entryId, 'userId' => 1])->all();
        $rankingData = [];

        foreach($rankingRecords as &$rankingRecord)
        {
            $keyTotalCount = RankingRecord::find()->where(['key' => $rankingRecord->key, 'entryId' => $entryId])->count();
            $keyLikedCount = RankingRecord::find()->where(['key' => $rankingRecord->key, 'entryId' => $entryId, 'liked' => 1])->count();
            $percentageLiked = ($keyTotalCount > 0) ? ($keyLikedCount / $keyTotalCount) * 100 : 0;

            array_push($rankingData, [
                'key' => $rankingRecord->key,
                'value' => strlen($rankingRecord->value) > 0 ? $rankingRecord->value : $rankingRecord->key,
                'percentLiked' => round($percentageLiked, 0)
            ]);
        }

        // Sort $rankingData by  percentageLiked

        usort($rankingData, function($a, $b)
        {
            return $b['percentLiked'] <=> $a['percentLiked'];
        });

        return $rankingData;
    }

    public function getRankItemLikePercent($entryId, $rankerKey)
    {
        $rankingRecords = RankingRecord::find()->where(['entryId' => $entryId, 'key' => $rankerKey])->all();
        $totalRecords = 0;
        $likedRecords = 0;
        $dislikedRecords = 0;

        foreach($rankingRecords as &$rankingRecord)
        {
            $totalRecords++;
            
            if($rankingRecord->liked === 1)
            {
                $likedRecords++;
            }
            elseif($rankingRecord->liked === 0)
            {
                $dislikedRecords++;
            }
            
        }

        // return $dislikedRecords;

        if($likedRecords > 0)
        {
            return round(($likedRecords / $totalRecords) * 100);
        }
        elseif($dislikedRecords > 0)
        {
            // Item has only received dislikes
            return 0;
        }
        else
        {
            // Show as 80-100% until users have started voting.
            return rand(80, 100);
        }
    }

    public function getRankTest()
    {
        // $count = 0;
        // $rankableEntries = Entry::find()->section('blog')->all();
        // foreach($rankableEntries as $entry)
        // {
        //     if($entry->enableRanking)
        //     {
        //         $count = $count + 1;
        //     }
        // }

        // return Entry::find()->section('blog')->type('internal')->enableRanking(true)->count();

        return count(Entry::findAll(['section' => 'blog']));
    }

    public function getRankEntries()
    {
        $rankableEntries = Entry::find()->section('blog')->enableRanking(true)->all();
        $rankingData = [];

        foreach($rankableEntries as $entry)
        {
            $currentUser = Craft::$app->getUser()->getIdentity();
            $rankingCount = RankingRecord::find()->where(['entryId' => $entry->id, 'userId' => $currentUser->id])->count();

            // if($rankingCount == 0)
            // {
                array_push($rankingData, [
                    'entryId' => $entry->id,
                    'entryTitle' => $entry->title,
                    'entryUrl' => $entry->url,
                    'blogImage' => $entry->blogImage->count() ? $entry->blogImage->one() : null,
                ]);
            // }
        }

        // array_push($rankingData, [
        //             'entryId' => 123,
        //             'entryTitle' => 'Article Title ' . $rankableEntries->count(),
        //             'entryUrl' => 'asdf',
        //             'blogImage' => null,
        //         ]);

        return $rankingData;
    }

    public function getCurrentUserKeys()
    {
        $currentUser = Craft::$app->getUser()->getIdentity();
        $rankingRecords = RankingRecord::find()->where(['userId' => $currentUser->id])->orderBy(['entryId' => SORT_ASC, 'dateUpdated' => SORT_DESC])->all();
        $rankingData = [];
        $entryId = 0;
        $entry = null;

        foreach($rankingRecords as &$rankingRecord)
        {
            if($entryId !== $rankingRecord->entryId)
            {
                $entryId = $rankingRecord->entryId;
                $entry = Entry::find()->id($entryId)->one();
            }

            array_push($rankingData, [
                'entryTitle' => $entry !== null ? $entry->title : '',
                'entryUrl' => $entry !== null ? $entry->url : '',
                'key' => $rankingRecord->key,
                'value' => $rankingRecord->value,
                'liked' => $rankingRecord->liked === null ? '' : $rankingRecord->liked,
                'sort' => $rankingRecord->sort === null ? '' : $rankingRecord->sort
            ]);
        }

        return $rankingData;
    }

    public function getCurrentUserLikedKeys()
    {
        $currentUser = Craft::$app->getUser()->getIdentity();
        $rankingRecords = RankingRecord::find()->where(['userId' => $currentUser->id, 'liked' => 1])->orderBy(['entryId' => SORT_ASC, 'dateUpdated' => SORT_DESC])->limit(50)->all();
        $rankingData = [];
        $entryId = 0;
        $entry = null;

        foreach($rankingRecords as &$rankingRecord)
        {
            if($entryId !== $rankingRecord->entryId)
            {
                $entryId = $rankingRecord->entryId;
                $entry = Entry::find()->id($entryId)->one();
            }

            array_push($rankingData, [
                'entryId' => $entry !== null ? $entry->id : 0,
                'entryTitle' => $entry !== null ? $entry->title : '',
                'entryUrl' => $entry !== null ? $entry->url : '',
                'key' => $rankingRecord->key,
                'value' => $rankingRecord->value,
                'liked' => $rankingRecord->liked === null ? '' : $rankingRecord->liked,
                'sort' => $rankingRecord->sort === null ? '' : $rankingRecord->sort
            ]);
        }

        return $rankingData;
    }

    public function getCurrentUserRankedKeys()
    {
        $currentUser = Craft::$app->getUser()->getIdentity();
        $rankingRecords = RankingRecord::find()->where(['userId' => $currentUser->id, 'liked' => [0, 1]])->orderBy(['entryId' => SORT_ASC, 'liked' => SORT_DESC, 'dateUpdated' => SORT_DESC])->limit(50)->all();
        $rankingData = [];
        $entryId = 0;
        $entry = null;

        foreach($rankingRecords as &$rankingRecord)
        {
            if($entryId !== $rankingRecord->entryId)
            {
                $entryId = $rankingRecord->entryId;
                $entry = Entry::find()->id($entryId)->one();
            }

            array_push($rankingData, [
                'entryId' => $entry !== null ? $entry->id : 0,
                'entryTitle' => $entry !== null ? $entry->title : '',
                'entryUrl' => $entry !== null ? $entry->url : '',
                'key' => $rankingRecord->key,
                'value' => $rankingRecord->value,
                'liked' => $rankingRecord->liked === null ? '' : $rankingRecord->liked,
                'sort' => $rankingRecord->sort === null ? '' : $rankingRecord->sort
            ]);
        }

        return $rankingData;
    }

    public function getCurrentUserKeysByEntry($entryId)
    {
        $currentUser = Craft::$app->getUser()->getIdentity();
        $rankingRecords = RankingRecord::find()->where(['userId' => $currentUser->id, 'entryId' => $entryId])->orderBy(['sort' => SORT_ASC])->all();
        $rankingData = [];

        foreach($rankingRecords as &$rankingRecord)
        {
            array_push($rankingData, [
                'key' => $rankingRecord->key,
                'value' => $rankingRecord->value,
                'liked' => $rankingRecord->liked === null ? '' : $rankingRecord->liked,
                'sort' => $rankingRecord->sort === null ? '' : $rankingRecord->sort
            ]);
        }

        return $rankingData;
    }

    public function likeCurrentUserKey($entryId, $key, $val, $liked)
    {
        $transaction = Craft::$app->getDb()->beginTransaction();

        try
        {
            $now = Db::prepareDateForDb(DateTimeHelper::now());
            $currentUser = Craft::$app->getUser()->getIdentity();
            $rankingRecord = RankingRecord::findOne(['userId' => $currentUser->id, 'entryId' => $entryId, 'key' => $key]) ?? new RankingRecord();

            if($rankingRecord->getIsNewRecord())
            {
                $rankingRecord->dateCreated = $now;
                $rankingRecord->userId = $currentUser->id;
                $rankingRecord->entryId = $entryId;
                $rankingRecord->key = $key;
                // $rankingRecord->value = $val;
                // $rankingRecord->sort = NULL;
                $rankingRecord->enabled = true;
            }

            $rankingRecord->value = $val; // Update in case name has changed
            $rankingRecord->dateUpdated = $now;
            if($liked === "liked")
            {
                $rankingRecord->liked = true;
            }
            elseif($liked === "disliked")
            {
                $rankingRecord->liked = false;
            }
            else
            {
                $rankingRecord->liked = NULL;
            }

            $rankingRecord->save();
            $transaction->commit();
        }
        catch (Throwable $e)
        {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    public function saveCurrentUserRankingOrder($entryId, $jsonData)
    {
        $transaction = Craft::$app->getDb()->beginTransaction();

        try
        {
            $rankingKeys = json_decode($jsonData);
            $now = Db::prepareDateForDb(DateTimeHelper::now());
            $currentUser = Craft::$app->getUser()->getIdentity();

            foreach($rankingKeys as $idx=>$key)
            {
                $rankingRecord = RankingRecord::findOne(['userId' => $currentUser->id, 'entryId' => $entryId, 'key' => $key]) ?? new RankingRecord();

                if($rankingRecord->getIsNewRecord())
                {
                    $rankingRecord->dateCreated = $now;
                    $rankingRecord->userId = $currentUser->id;
                    $rankingRecord->entryId = $entryId;
                    $rankingRecord->key = $key;
                    $rankingRecord->enabled = true;
                }

                $rankingRecord->dateUpdated = $now;
                $rankingRecord->sort = $idx;

                $rankingRecord->save();
            }

            $transaction->commit();
        }
        catch (Throwable $e)
        {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    public function getEpkBySlug($slug)
    {
        $epk = null;

        if($epkRecord = EpkRecord::findOne(['slug' => $slug]))
        {
            if($bandRecord = BandRecord::findOne(['id' => $epkRecord->bandId]))
            {
                $epk = new EpkModel($bandRecord, $epkRecord, true);

                if($bandRecord->genres)
                {
                    if($genreIds = json_decode($bandRecord->genres))
                    {
                        foreach($genreIds as &$genreId)
                        {
                            $genreEntry = Entry::find()->id($genreId)->one();
                            array_push($epk->genres, $genreEntry->title);
                        }
                    }
                }
            }
        }

        return $epk;
    }

    public function getCurrentUserBand()
    {
        $currentUser = Craft::$app->getUser()->getIdentity();
        $bandRecord = BandRecord::findOne(['userId' => $currentUser->id]) ?? new BandRecord();
        $genreEntries = Entry::find()->section('genres')->orderBy('title');
        $bandRecordGenres = [];
        $allGenres = [];
        $genres = [];

        if($bandRecordExists = !$bandRecord->getIsNewRecord())
        {
            $bandRecordGenres = json_decode($bandRecord->genres ?? null, true) ?? [];
        }

        foreach($genreEntries as &$ge)
        {
            if($checked = in_array($ge->id, $bandRecordGenres))
            {
                array_push($genres, $ge);
            }
            array_push($allGenres, [
                'id' => $ge->id,
                'title' => $ge->title,
                'checked' => $checked
            ]);
        }

        return [
            'exists' => $bandRecordExists,
            'name' => $bandRecord->name,
            'websiteUrl' => $bandRecord->websiteUrl,
            'phone' => $bandRecord->phone,
            'email' => $bandRecord->email,
            'description' => $bandRecord->description,
            'logo' => $bandRecord->logoId == null ? null : (Craft::$app->getAssets()->getAssetById($bandRecord->logoId) ?? null),
            'genres' => $genres,
            'allGenres' => $allGenres
        ];
    }

    public function getCurrentUserEpkModel()
    {
        $epk = null;
        $currentUser = Craft::$app->getUser()->getIdentity();

        if($epkRecord = EpkRecord::findOne(['userId' => $currentUser->id]))
        {
            if($bandRecord = BandRecord::findOne(['id' => $epkRecord->bandId]))
            {
                $epk = new EpkModel($bandRecord, $epkRecord, true);

                // if slug doesn't exist, generate one from band name

                if(trim(strlen($epk->slug)) == 0 && trim(strlen($bandRecord->name)) > 0)
                {
                    $epk->slug = ElementHelper::generateSlug($bandRecord->name, null, 'en');
                }
            }
        }
        else if($bandRecord = BandRecord::findOne(['userId' => $currentUser->id]))
        {
            $epk = new EpkModel($bandRecord);
        }

        return $epk;
    }

    public function saveCurrentUserBand($band)
    {
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            $now = Db::prepareDateForDb(DateTimeHelper::now());
            $currentUser = Craft::$app->getUser()->getIdentity();
            $bandRecord = BandRecord::findOne(['userId' => $currentUser->id]) ?? new BandRecord();

            if($bandRecord->getIsNewRecord())
            {
                $bandRecord->dateCreated = $now;
                $bandRecord->userId = $currentUser->id;
            }
            $bandRecord->dateUpdated = $now;
            $bandRecord->name = $band['name'];
            $bandRecord->websiteUrl = $band['websiteUrl'];
            $bandRecord->phone = $band['phone'];
            $bandRecord->email = $band['email'];
            $bandRecord->description = $band['description'];
            $bandRecord->genres = json_encode($band['genres']);
            if($band['logoId'])
            {
                $bandRecord->logoId = $band['logoId'];
            }
            
            $bandRecord->save();

            $transaction->commit();

        }
        catch (Throwable $e)
        {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    public function saveCurrentUserEpk($epk)
    {
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {

            $now = Db::prepareDateForDb(DateTimeHelper::now());
            $currentUser = Craft::$app->getUser()->getIdentity();
            $bandRecord = BandRecord::findOne(['userId' => $currentUser->id]);
            $epkRecord = EpkRecord::findOne(['userId' => $currentUser->id]) ?? new EpkRecord();

            if($epkRecord->getIsNewRecord())
            {
                $epkRecord->dateCreated = $now;
                $epkRecord->userId = $currentUser->id;
            }
            if($bandRecord) $epkRecord->bandId = $bandRecord->id;
            $epkRecord->dateUpdated = $now;
            $epkRecord->enabled = $epk['enabled'] ?? false;
            $epkRecord->bio = $epk['bio'];
            $epkRecord->cta = $epk['cta'];
            $epkRecord->slug = $epk['slug'];
            $epkRecord->requirements = $epk['requirements'];
            $epkRecord->insurance = (empty(trim($epk['insurance']['amount'])) && empty(trim($epk['insurance']['description']))) ? "" : json_encode($epk['insurance']);
            $epkRecord->priceRange = (empty(trim($epk['price']['min'])) && empty(trim($epk['price']['max']))) ? "" : json_encode($epk['price']);
            $epkRecord->gigLength = (empty(trim($epk['length']['min'])) && empty(trim($epk['length']['max']))) ? "" : json_encode($epk['length']);
            $epkRecord->socialMedia = json_encode($epk['social']);

            $epkRecord->save();

            $transaction->commit();

        }
        catch (Throwable $e)
        {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    public function saveCurrentUserBandLogo($fileLocation, $filename)
    {
        $currentUser = Craft::$app->getUser()->getIdentity();
        $filename = AssetsHelper::prepareAssetName($filename ?? pathinfo($fileLocation, PATHINFO_BASENAME), true, true);
        $assetsService = Craft::$app->getAssets();
        $logoId = null;

        if (!Image::canManipulateAsImage(pathinfo($fileLocation, PATHINFO_EXTENSION)))
        {
            throw new ImageException(Craft::t('app', 'User photo must be an image that Craft can manipulate.'));
        }

        $assetsService = Craft::$app->getAssets();

        $bandRecord = BandRecord::findOne(['userId' => $currentUser->id]);

        // If the photo exists, just replace the file.
        if ($bandRecord && $bandRecord->logoId)
        {
            $logoId = $bandRecord->logoId;

            $oldLogo = Craft::$app->getAssets()->getAssetById($logoId) ?? false;
            $assetsService->replaceAssetFile($oldLogo, $fileLocation, $filename);
        }
        else
        {
            $volume = Craft::$app->getVolumes()->getVolumeByHandle(self::IMAGE_VOLUME_HANDLE);

            $folderId = Craft::$app->getAssets()->ensureFolderByFullPathAndVolume($this->getUserVolumePath($currentUser), $volume)->id;
            $filename = $assetsService->getNameReplacementInFolder($filename, $folderId);

            $logo = new Asset();
            $logo->setScenario(Asset::SCENARIO_CREATE);
            $logo->tempFilePath = $fileLocation;
            $logo->setFilename($filename);
            $logo->newFolderId = $folderId;
            $logo->setVolumeId($volume->id);

            $elementsService = Craft::$app->getElements();
            $elementsService->saveElement($logo);

            $logoId = $logo->id;
        }

        return $logoId;
    }

    public function deleteBandLogo($logoId)
    {
        $transaction = Craft::$app->getDb()->beginTransaction();

        try 
        {
            $logoDeleted = Craft::$app->getElements()->deleteElementById($logoId, Asset::class);

            if ($logoDeleted)
            {
                if($bandRecord = BandRecord::findOne(['logoId' => $logoId]))
                {
                    $now = Db::prepareDateForDb(DateTimeHelper::now());
                    $bandRecord->logoId = null;
                    $bandRecord->dateUpdated = $now;
                    $bandRecord->save();                    
                }
            }

            $transaction->commit();
        }
        catch (Throwable $e)
        {
            $transaction->rollBack();
            
            throw $e;

            return false;
        }

        return true;
    }

    public function saveCurrentUserEpkImage($fileLocation, $filename, $caption)
    {
        $transaction = Craft::$app->getDb()->beginTransaction();

        try
        {
            $currentUser = Craft::$app->getUser()->getIdentity();
            $filename = AssetsHelper::prepareAssetName($filename ?? pathinfo($fileLocation, PATHINFO_BASENAME), true, true);
            $assetsService = Craft::$app->getAssets();

            if (!Image::canManipulateAsImage(pathinfo($fileLocation, PATHINFO_EXTENSION)))
            {
                throw new ImageException(Craft::t('app', 'User photo must be an image that Craft can manipulate.'));
            }

            if($epkRecord = EpkRecord::findOne(['userId' => $currentUser->id]))
            {
                $images = empty($epkRecord->images) ? [] : json_decode($epkRecord->images);
            }
            else
            {
                $images = [];
            }

            $assetsService = Craft::$app->getAssets();
            $volume = Craft::$app->getVolumes()->getVolumeByHandle(self::IMAGE_VOLUME_HANDLE);
            $folderId = Craft::$app->getAssets()->ensureFolderByFullPathAndVolume($this->getUserVolumePath($currentUser), $volume)->id;
            $filename = $assetsService->getNameReplacementInFolder($filename, $folderId);

            $img = new Asset();
            $img->setScenario(Asset::SCENARIO_CREATE);
            $img->tempFilePath = $fileLocation;
            $img->setFilename($filename);
            $img->newFolderId = $folderId;
            $img->setVolumeId($volume->id);

            $elementsService = Craft::$app->getElements();
            $elementsService->saveElement($img);

            array_push($images, json_encode(array("caption"=>$caption, "id"=>$img->id)));
                
            $this->updateCurrentUserEpkProperty('images', json_encode($images));

            $transaction->commit();
        }
        catch (Throwable $e)
        {
            $transaction->rollBack();
            throw $e;
            return false;
        }

    }

    public function deleteCurrentUserEpkImage($imgId)
    {
        $transaction = Craft::$app->getDb()->beginTransaction();

        try
        {
            $currentUser = Craft::$app->getUser()->getIdentity();
            $images = [];
            $epkImageFound = false;

            if($epkRecord = EpkRecord::findOne(['userId' => $currentUser->id]))
            {
                $epkImages = empty($epkRecord->images) ? [] : json_decode($epkRecord->images);

                foreach($epkImages as &$img)
                {
                    if(json_decode($img)->id == $imgId)
                    {
                        $epkImageFound = true;
                    }
                    else
                    {
                        array_push($images, $img);
                    }
                }
            }

            // Don't remove images unless found in THIS user's collection
            if($epkImageFound)
            {
                $imgDeleted = Craft::$app->getElements()->deleteElementById($imgId, Asset::class);
            }

            $this->updateCurrentUserEpkProperty('images', json_encode($images));
            $transaction->commit();
        }
        catch (Throwable $e)
        {
            $transaction->rollBack();
            throw $e;
            return false;
        }
    }

    public function saveCurrentUserEpkVideo($videoTitle, $videoUrl)
    {
        $videoId = $this->getYoutubeIdFromUrl($videoUrl);

        if($videoId)
        {
            $currentUser = Craft::$app->getUser()->getIdentity();

            if($epkRecord = EpkRecord::findOne(['userId' => $currentUser->id]))
            {
                $videos = empty($epkRecord->videos) ? [] : json_decode($epkRecord->videos);
            }
            else
            {
                $videos = [];
            }

            $cleanTitle = $this->cleanTitle($videoTitle);

            array_push($videos, json_encode(array("title"=>$cleanTitle, "id"=>$videoId)));

            $this->updateCurrentUserEpkProperty('videos', json_encode($videos));
        }
    }

    public function deleteCurrentUserEpkVideo($videoId)
    {
        $transaction = Craft::$app->getDb()->beginTransaction();

        try
        {
            $currentUser = Craft::$app->getUser()->getIdentity();

            if ($epkRecord = EpkRecord::findOne(['userId' => $currentUser->id]))
            {
                $videos = empty($epkRecord->videos) ? [] : json_decode($epkRecord->videos, true);
                
                $videos = array_filter($videos, function ($filteredVideo) use ($videoId)
                {
                    return json_decode($filteredVideo, true)['id'] !== $videoId;
                });

                $this->updateCurrentUserEpkProperty('videos', json_encode($videos));
                $transaction->commit();
            }
        }
        catch (Throwable $e)
        {
            $transaction->rollBack();
            throw $e;
            return false;
        }
    }

    public function saveCurrentUserEpkSong($songTitle, $songEmbedCode)
    {
        $songId = uniqid();
        $songTitle = trim($this->cleanTitle($songTitle));
        $songEmbedCode = trim($this->extractIframe($songEmbedCode));

        if(strlen($songTitle) > 0 && strlen($songEmbedCode) > 0)
        {
            $currentUser = Craft::$app->getUser()->getIdentity();

            if($epkRecord = EpkRecord::findOne(['userId' => $currentUser->id]))
            {
                $songs = empty($epkRecord->songs) ? [] : json_decode($epkRecord->songs);
            }
            else
            {
                $songs = [];
            }

            array_push($songs, json_encode(array("id"=>$songId, "title"=>$songTitle, "embed"=>$songEmbedCode)));

            $this->updateCurrentUserEpkProperty('songs', json_encode($songs));
        }
        else
        {
            Craft::$app->getSession()->setError("Song title or embed code were missing or invalid. $msg");
        }
    }

    public function deleteCurrentUserEpkSong($songId)
    {
        $transaction = Craft::$app->getDb()->beginTransaction();

        try
        {
            $currentUser = Craft::$app->getUser()->getIdentity();
            $songs = [];

            if($epkRecord = EpkRecord::findOne(['userId' => $currentUser->id]))
            {
                $epkSongs = empty($epkRecord->songs) ? [] : json_decode($epkRecord->songs);

                foreach($epkSongs as &$song)
                {
                    if(json_decode($song)->id != $songId)
                    {
                        array_push($songs, $song);
                    }
                }
            }

            $this->updateCurrentUserEpkProperty('songs', json_encode($songs));
            $transaction->commit();
        }
        catch (Throwable $e)
        {
            $transaction->rollBack();
            throw $e;
            return false;
        }
    }

    

    public function validateText(array $vals, $msg = "", $required = false):bool
    {
        $isValid = true;
        $numberOfEmpty = 0;

        foreach($vals as &$val)
        {
            if(trim($val) != '' && $val != strip_tags($val))
            {
                $isValid = false;
            }
            else if($required && strlen(trim($val)) == 0)
            {
                $numberOfEmpty++;
            }
        }

        if(!$isValid)
        {
            Craft::$app->getSession()->setError("Invalid characters detected. $msg");
        }
        else if($required && $numberOfEmpty > 0)
        {
            $isValid = false;
            Craft::$app->getSession()->setError("Required fields were empty. $msg");
        }

        return $isValid;
    }

    public function validateSlug($slug)
    {
        //$this->validateSlug($uniqueSlug);
        return true;
    }

    public function validateUrl(array $urls, $msg = ""):bool
    {
        $isValid = true;

        foreach($urls as &$url)
        {
            if(strlen(trim($url)) > 0 && !str_starts_with($url, 'http'))
            {
                $isValid = false;
            }
        }

        if(!$isValid)
        {
            Craft::$app->getSession()->setError("URL must start with https:// or http:// $msg");
        }

        return $isValid;
    }

    public function validateImage(array $imgs, $msg = ""):bool
    {
        $isValid = true;

        foreach($imgs as &$img)
        {
            if($img->size > self::MAX_IMAGE_SIZE)
            {
                $isValid = false;
            }
        }

        if(!$isValid)
        {
            Craft::$app->getSession()->setError("That's a big file! Images may not be over 0.5 MB. $msg");
        }

        return $isValid;
    }
}
