<?php
/**
 * Band Pioneer Craft CMS 4.x
 *
 * Band Pioneer plugin for custom website function.
 *
 * @link      https://bandpioneer.com
 * @copyright Copyright (c) 2023 Band Pioneer
 */

namespace bandpioneer\rockstar\controllers;

use bandpioneer\rockstar\Rockstar;
use bandpioneer\rockstar\services\RockstarService;

use Craft;
use craft\web\Controller;

use yii\web\Response;
use yii\log\Logger;

/**
 * @author    Band Pioneer
 * @package   Bands
 * @since     1.0.0
 */
class KeywordsController extends Controller
{
	public function actionData(): string
    {
    	$this->requireLogin();

    	$request = Craft::$app->getRequest();

    	$keyword = $request->getParam('keyword');

    	$service = Rockstar::$plugin->getKeywordService();

    	$keywordHtml = $service->getKeywordDataHtml($keyword);

        return $keywordHtml;
    }

    public function actionRelated(): string
    {
    	$request = Craft::$app->getRequest();

    	$keyword = $request->getParam('keyword');

    	$service = Rockstar::$plugin->getKeywordService();

    	$html = $service->getRelatedKeywordHtml($keyword);

        return $html;
    }
}