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
use bandpioneer\rockstar\records\EpkRecord as EpkRecord;

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
        // preg_match("/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user|shorts)\/))([^\?&\"'>]+)/", $url, $matches);
        // preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+(?=\?)|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $link, $matches);

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

    /*** PUBLIC MEMBERS ***/

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
            $bandRecordGenres = json_decode($bandRecord->genres, true) ?? [];
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

    public function getCurrentUserEpk()
    {
        $currentUser = Craft::$app->getUser()->getIdentity();
        $epkRecordExists = false;
        $bio = '';
        $cta = '';
        $requirements = '';
        $videos = [];
        $images = [];
        $insurance = [
            'amount' => '',
            'description' => ''
        ];
        $price = [
            'min' => '',
            'max' => ''
        ];
        $length = [
            'min' => '',
            'max' => ''
        ];

        if($epkRecord = EpkRecord::findOne(['userId' => $currentUser->id]))
        {
            $epkRecordExists = true;

            $bio = $epkRecord->bio ?? $bio;
            $cta = $epkRecord->cta ?? $cta;
            $requirements = $epkRecord->requirements ?? $requirements;

            $insurance = json_decode($epkRecord->insurance, false) ?? $insurance;
            $price = json_decode($epkRecord->priceRange, false) ?? $price;
            $length = json_decode($epkRecord->gigLength, false) ?? $length;
            $social = json_decode($epkRecord->socialMedia, false) ?? [];

            if($epkRecord->videos)
            {
                $epkVideos = json_decode($epkRecord->videos, false);
                foreach($epkVideos as &$jsonVideo)
                {
                    array_push($videos, json_decode($jsonVideo));
                }
            }

            if($epkRecord->images)
            {
                $epkImages = json_decode($epkRecord->images, false);
                foreach($epkImages as &$jsonImg)
                {
                    $img = json_decode($jsonImg);
                    $img->image = Craft::$app->getAssets()->getAssetById(json_decode($jsonImg)->id) ?? false;
                    if($img->image)
                    {
                        array_push($images, $img);
                    }
                }
            }
        }
        
        return [
            'exists' => $epkRecordExists,
            'videos' => $videos,
            'images' => $images,
            'bio' => $bio,
            'cta' => $cta,
            'requirements' => $requirements,
            'insurance' => $insurance,
            'price' => $price,
            'length' => $length,
            'social' => $social
        ];
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
            $epkRecord->bio = $epk['bio'];
            $epkRecord->cta = $epk['cta'];
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

            $cleanTitle = str_replace( array( '\'', '"', ',' , ';', '<', '>' ), ' ', $videoTitle);

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
            $videos = [];

            if($epkRecord = EpkRecord::findOne(['userId' => $currentUser->id]))
            {
                $epkVideos = empty($epkRecord->videos) ? [] : json_decode($epkRecord->videos);

                foreach($epkVideos as &$video)
                {
                    if(json_decode($video)->id != $videoId)
                    {
                        array_push($videos, $video);
                    }
                }
            }

            $this->updateCurrentUserEpkProperty('videos', json_encode($videos));
            $transaction->commit();
        }
        catch (Throwable $e)
        {
            $transaction->rollBack();
            throw $e;
            return false;
        }
    }

    public function validateText(array $vals, $msg = ""):bool
    {
        $isValid = true;

        foreach($vals as &$val)
        {
            if(trim($val) != '' && $val != strip_tags($val))
            {
                $isValid = false;
            }
        }

        if(!$isValid)
        {
            Craft::$app->getSession()->setError("Invalid characters detected. $msg");
        }

        return $isValid;
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
