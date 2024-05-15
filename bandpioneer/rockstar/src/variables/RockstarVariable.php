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

class RockstarVariable
{
	public function getCurrentUserRanking($entryId)
    {
    	return Rockstar::$plugin->getService()->getCurrentUserRankingData($entryId);
    }
}