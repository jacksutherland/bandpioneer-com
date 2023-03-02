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
        $bandRecord = $service->getCurrentUserBand();
        $epkRecord = $service->getCurrentUserEpk();

        return $this->renderTemplate('bands/dashboard', [
            'band' => [
                'name' => $bandRecord->name,
                'websiteUrl' => $bandRecord->websiteUrl,
                'phone' => $bandRecord->phone,
                'email' => $bandRecord->email,
                'description' => $bandRecord->description,
                'logo' => $bandRecord->logoId == null ? null : (Craft::$app->getAssets()->getAssetById($bandRecord->logoId) ?? null)
            ],
            'epk' => $epkRecord
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

    public function actionSaveEpkInfo(): ?Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $session = Craft::$app->getSession();
        $service = Rockstar::$plugin->getService();

        $epk = [
            'bio' => $request->getParam('bio'),
            'requirements' => $request->getParam('requirements'),
            'insurance' => [
                'amount' => $request->getParam('insurance[amount]'),
                'description' => $request->getParam('insurance[description]')
            ],
            'price' => [
                'min' => $request->getParam('price[min]'),
                'max' => $request->getParam('price[max]')
            ],
            'length' => [
                'min' => $request->getParam('length[min]'),
                'max' => $request->getParam('length[max]')
            ]
        ];

        $service->saveCurrentUserEpk($epk);

        return $this->redirect('bands/dashboard');
    }

    public function actionSaveImage(): ?Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $service = Rockstar::$plugin->getService();

        $caption = $request->getParam('caption');
        
        if($img = UploadedFile::getInstanceByName('image'))
        {
            $imgLocation = Assets::tempFilePath($img->getExtension());
            move_uploaded_file($img->tempName, $imgLocation);

            $imgId = $service->saveCurrentUserEpkImage($imgLocation, $img->name, $caption);
        }

        return $this->redirect('bands/dashboard');
    }

    public function actionDeleteImage(): ?Response
    {
        $this->requirePostRequest();

        $service = Rockstar::$plugin->getService();
        $request = Craft::$app->getRequest();
        $imgId = $request->getParam('id');

        $service->deleteCurrentUserEpkImage($imgId);

        return $this->redirect('bands/dashboard');
    }

    public function actionSaveVideo(): ?Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $title = $request->getParam('title');
        $url = $request->getParam('url');

        if(!empty($title) && !empty($url))
        {
            $service = Rockstar::$plugin->getService();

            $service->saveCurrentUserEpkVideo($title, $url);
        }

        return $this->redirect('bands/dashboard');
    }

    public function actionDeleteVideo(): ?Response
    {
        $this->requirePostRequest();

        $service = Rockstar::$plugin->getService();
        $request = Craft::$app->getRequest();
        $videoId = $request->getParam('id');

        $service->deleteCurrentUserEpkVideo($videoId);

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
