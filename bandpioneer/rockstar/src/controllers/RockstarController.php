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
    public function actionData(): string
    {
        $this->requireLogin();

        // $request = Craft::$app->getRequest();

        // $keyword = $request->getParam('keyword');

        // $service = Rockstar::$plugin->getKeywordService();

        // $keywordHtml = $service->getKeywordDataHtml($keyword);

        return "This is Rockstar Controller Action Data";
    }

	public function actionSaveRanking(): string
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

            $service->saveCurrentUserRankingData($entryId, $data);

            return "success";
        }

        return "error";
    }
}