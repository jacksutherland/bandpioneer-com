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
                    $bandRecord->logoId = null;
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

}
