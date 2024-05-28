<?php
/**
 * Band Pioneer Craft CMS 5.x
 *
 * Band Pioneer plugin for custom website function.
 *
 * @link      https://bandpioneer.com
 * @copyright Copyright (c) 2024 Band Pioneer
 */

namespace bandpioneer\rockstar\controllers;

use bandpioneer\rockstar\Rockstar;
use bandpioneer\rockstar\services\RockstarService;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;

use yii\web\Response;
use yii\log\Logger;

/**
 * @author    Band Pioneer
 * @package   Bands
 * @since     1.0.0
 */
class RockstarController extends Controller
{
    public function actionSaveRankingOrder(): string
    {
        $this->requirePostRequest();

        $service = Rockstar::$plugin->getService();
        $request = Craft::$app->getRequest();
        $entryId = trim($request->getParam('eid'));
        $data = trim($request->getParam('data'));

        /*** VALIDATION ***/

        if (is_numeric($entryId) && is_string($data) && !empty($data))
        {
            /*** SAVE ***/

            $service->saveCurrentUserRankingOrder($entryId, $data);

            return "success";
        }

        return "error";
    }

    public function actionRankingLikeIt(): string
    {
        $this->requirePostRequest();

        $service = Rockstar::$plugin->getService();
        $request = Craft::$app->getRequest();
        $entryId = trim($request->getParam('eid'));
        $key = trim($request->getParam('key'));
        $val = trim($request->getParam('val'));
        $liked = trim($request->getParam('liked'));

        /*** VALIDATION ***/

        if (is_numeric($entryId) && is_string($key) && !empty($key) && is_string($liked) && !empty($liked))
        {
            /*** SAVE ***/

            $service->likeCurrentUserKey($entryId, $key, $val, $liked);

            return "success";
        }

        return "error";
    }
}