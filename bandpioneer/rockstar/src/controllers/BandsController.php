<?php
/**
 * Band Pioneer Craft CMS 4.x
 *
 * Band Pioneer plugin for base website design properties.
 *
 * @link      https://bandpioneer.com
 * @copyright Copyright (c) 2023 Band Pioneer
 */

namespace bandpioneer\rockstar\controllers;

use bandpioneer\rockstar\Rockstar;
use bandpioneer\rockstar\services\RockstarService;

use Craft;
use craft\web\Controller;
use craft\web\UploadedFile;
use craft\helpers\Json;
use craft\helpers\Image;
use craft\helpers\Assets;

// use yii\web\UploadedFile;
use yii\web\Response;
use yii\log\Logger;

/**
 * @author    Band Pioneer
 * @package   Bands
 * @since     1.0.0
 */
class BandsController extends Controller
{
    protected array|bool|int $allowAnonymous = [
        'public-action', 'dashboard'
    ];

    public function actionDashboard(): Response
    {
        $this->requireLogin();

        $service = Rockstar::$plugin->getService();
        $record = $service->getCurrentUserBand();
        $band = [
            'name' => $record->name,
            'websiteUrl' => $record->websiteUrl,
            'phone' => $record->phone,
            'email' => $record->email,
            'description' => $record->description,
            'logo' => $record->logoId == null ? null : (Craft::$app->getAssets()->getAssetById($record->logoId) ?? null)
        ];

        return $this->renderTemplate('bands/dashboard', [
            'band' => $band
        ]);
    }

    public function actionSaveBand(): ?Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $session = Craft::$app->getSession();
        $service = Rockstar::$plugin->getService();
        $logoId = null;
        

        if($logo = UploadedFile::getInstanceByName('logo'))
        {
            $logoLocation = Assets::tempFilePath($logo->getExtension());
            move_uploaded_file($logo->tempName, $logoLocation);

            $logoId = $service->saveCurrentUserBandLogo($logoLocation, $logo->name);
        }

        $band = [
            'name' => $request->getParam('name'),
            'websiteUrl' => $request->getParam('websiteUrl'),
            'phone' => $request->getParam('phone'),
            'email' => $request->getParam('email'),
            'description' => $request->getParam('description'),
            'logoId' => $logoId
        ];

        $service->saveCurrentUserBand($band);

        return $this->redirect('bands/dashboard');
    }

    public function actionDeleteLogo()
    {
        $this->requirePostRequest();


        $service = Rockstar::$plugin->getService();
        $request = Craft::$app->getRequest();
        $logoId = $request->getParam('id');

        if($service->deleteBandLogo($logoId))
        {
            return $this->asJson([
                'result' => 'success'
            ]);
        }

        return $this->asJson([
            'result' => 'error'
        ]);
    }
}