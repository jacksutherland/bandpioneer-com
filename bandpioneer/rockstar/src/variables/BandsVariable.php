<?php
namespace bandpioneer\rockstar\variables;

use bandpioneer\rockstar\Rockstar;

use Craft;
use craft\elements\Asset;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Html;
use craft\helpers\Json;
use craft\helpers\Template;

use Twig\Markup;

class BandsVariable
{
    // Check if band object has been created yet for the current user
	public function exists()
    {
    	return Rockstar::$plugin->getService()->bandExistsForCurrentUser();
    }

    public function getEpk($slug)
    {
        return Rockstar::$plugin->getService()->getEpkBySlug($slug);
    }

    public function chatQuery($question)
    {
        return Rockstar::$plugin->getAIService()->chatQuery($question);
    }

    public function getKeywordList()
    {
        return Rockstar::$plugin->getAIService()->getKeywordList();
    }

    public function getKeywordTitle($path)
    {
        return Rockstar::$plugin->getAIService()->getKeywordTitle($path);
    }

    public function getKeywordBody($path)
    {
        return Rockstar::$plugin->getAIService()->getKeywordBody($path);
    }

    public function getKeywordDescription($path)
    {
        return Rockstar::$plugin->getAIService()->getKeywordDescription($path);
    }
}