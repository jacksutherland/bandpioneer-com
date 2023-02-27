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
    const LOGO_VOLUME_HANDLE = 'rockstarImages';

    public function getCurrentUserBand()
    {
        $currentUser = Craft::$app->getUser()->getIdentity();
        $bandRecord = BandRecord::findOne(['userId' => $currentUser->id]) ?? new BandRecord();

        return $bandRecord;
    }

    public function getCurrentUserEpk()
    {
        $currentUser = Craft::$app->getUser()->getIdentity();

        if($epkRecord = EpkRecord::findOne(['userId' => $currentUser->id]))
        {
            $epkVideos = json_decode($epkRecord->videos, false);
            $videos = [];
            foreach($epkVideos as &$jsonVideo)
            {
                array_push($videos, json_decode($jsonVideo));
            }

            return [
                'videos' => $videos
            ];
        }

        return [];
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
            $volume = Craft::$app->getVolumes()->getVolumeByHandle(self::LOGO_VOLUME_HANDLE);
            $folderId = Craft::$app->getAssets()->ensureFolderByFullPathAndVolume("", $volume)->id;
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

    public function saveCurrentUserBandVideo($videoTitle, $videoUrl)
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

            array_push($videos, json_encode(array("title"=>$videoTitle, "id"=>$videoId)));

            $this->updateCurrentUserEpkProperty('videos', json_encode($videos));
        }
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

}
