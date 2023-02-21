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

use bandpioneer\rockstar\Bands;

use Craft;
use craft\web\Controller;
use craft\helpers\Json;

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
        'public'
    ];

    public function actionPublic()
    {
        return 'public controller action test';
    }

    public function actionPrivate()
    {
        return 'private controller action test';
    }

    // public function actionLog(?string $method = null, ?string $firstname = null, ?string $lastname = null, ?string $email = null, ?string $phone = null, ?string $company = null): Response
    // {
    //     // $this->requireAcceptsJson();
    //     // $this->requirePostRequest();
    //     // $request = Craft::$app->getRequest();
    //     // $method = $request->getParam("method");

    //     $message = "method={$method}";

    //     if($method == 'onFormSubmit')
    //     {
    //         // $company = $request->getParam("company");
    //         // $firstname = $request->getParam("firstname");
    //         // $lastname = $request->getParam("lastname");
    //         // $phone = $request->getParam("phone");
    //         // $email = $request->getParam("email");

    //         $message .= ",firstname={$firstname},lastname={$lastname},phone={$phone},email={$email},company={$company}";
    //     }

    //     Craft::getLogger()->log('GO TEAM: ' . $message, \yii\log\Logger::LEVEL_INFO, 'bandpioneer\rockstar');

    //     return $this->asJson([
    //         'success' => true
    //     ]);
    // }
}
