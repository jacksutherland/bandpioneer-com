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
            'band' => $bandRecord,
            'epk' => $epkRecord
        ]);
    }

    public function actionSaveBand(): ?Response
    {
        $this->requirePostRequest();

        $service = Rockstar::$plugin->getService();
        $request = Craft::$app->getRequest();
        $name = trim($request->getParam('name'));
        $websiteUrl = trim($request->getParam('websiteUrl'));
        $phone = trim($request->getParam('phone'));
        $email = trim($request->getParam('email'));
        $description = trim($request->getParam('description'));
        $genres = $request->getParam('genres');

        /*** VALIDATION ***/

        if(empty($name))
        {
            Craft::$app->getSession()->setError("A band name is required");
            return $this->redirect('bands/dashboard/edit/band');
        }

        if(!$service->validateText([$name, $websiteUrl, $phone, $email, $description], 'Band not saved.'))
        {
            return $this->redirect('bands/dashboard/edit/band');
        }

        /*** SAVE ***/

        $session = Craft::$app->getSession();
        $logoId = null;

        if($logo = UploadedFile::getInstanceByName('logo'))
        {
            if(!$service->validateImage([$logo]))
            {
                return $this->redirect('bands/dashboard/edit/band');
            }

            $logoLocation = Assets::tempFilePath($logo->getExtension());
            move_uploaded_file($logo->tempName, $logoLocation);

            $logoId = $service->saveCurrentUserBandLogo($logoLocation, $logo->name);
        }

        $band = [
            'name' => $name,
            'websiteUrl' => $websiteUrl,
            'phone' => $phone,
            'email' => $email,
            'description' => $description,
            'logoId' => $logoId,
            'genres' => $genres
        ];

        $service->saveCurrentUserBand($band);

        Craft::$app->getSession()->setNotice("Band saved successfully.");

        return $this->redirect('bands/dashboard');
    }

    public function actionSaveEpkInfo(): ?Response
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $session = Craft::$app->getSession();
        $service = Rockstar::$plugin->getService();

        $epk = [
            'enabled' => $request->getParam('enabled'),
            'genres' => $request->getParam('genres'),
            'bio' => $request->getParam('bio'),
            'cta' => $request->getParam('cta'),
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
            ],
            'social' => [
                'bandcamp' => $request->getParam('social[bandcamp]'),
                'facebook' => $request->getParam('social[facebook]'),
                'instagram' => $request->getParam('social[instagram]'),
                'soundcloud' => $request->getParam('social[soundcloud]'),
                'tiktok' => $request->getParam('social[tiktok]'),
                'twitter' => $request->getParam('social[twitter]'),
                'youtube' => $request->getParam('social[youtube]')
            ]
        ];

        if(!$service->validateUrl([$epk['social']['bandcamp'], $epk['social']['facebook'], $epk['social']['instagram'], $epk['social']['soundcloud'], $epk['social']['tiktok'], $epk['social']['twitter'], $epk['social']['youtube']], 'EPK not saved.'))
        {
            return $this->redirect('bands/dashboard/edit/epk');
        }

        $service->saveCurrentUserEpk($epk);

        Craft::$app->getSession()->setNotice("EPK saved successfully.");

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
            if(!$service->validateImage([$img]))
            {
                return $this->redirect('bands/dashboard/edit/band');
            }

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
