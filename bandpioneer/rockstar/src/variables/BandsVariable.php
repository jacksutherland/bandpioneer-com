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
	public function getEpk($slug)
    {
    	return Rockstar::$plugin->getService()->getEpkBySlug($slug);
    }

    public function chatQuery($question)
    {
        return Rockstar::$plugin->getAIService()->chatQuery($question);
    }
}