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
	public function getRankEntries()
    {
    	return Rockstar::$plugin->getService()->getRankEntries();
    }

    public function getRankTest()
    {
        return Rockstar::$plugin->getService()->getRankTest();
    }

    public function getUserOrderedItemsForEntry($entryId)
    {
        return Rockstar::$plugin->getService()->getUserOrderedItemsForEntry($entryId);
    }

    public function getCurrentUserLikedKeys()
    {
        return Rockstar::$plugin->getService()->getCurrentUserLikedKeys();
    }

    public function getCurrentUserRankedKeys()
    {
        return Rockstar::$plugin->getService()->getCurrentUserRankedKeys();
    }

    public function getCurrentUserKeysByEntry($entryId)
    {
        return Rockstar::$plugin->getService()->getCurrentUserKeysByEntry($entryId);
    }

    public function getRankItemLikePercent($entryId, $rankerKey)
    {
        return Rockstar::$plugin->getService()->getRankItemLikePercent($entryId, $rankerKey);
    }
}