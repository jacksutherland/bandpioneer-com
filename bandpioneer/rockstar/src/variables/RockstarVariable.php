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

    public function getEntryItemsOrderedByLikes($entryId, $getAdminData = false)
    {
        // $getAdminData==true, returns data for just Jack
        // $getAdminData==false, returns data for all users
        if($getAdminData)
        {
            return Rockstar::$plugin->getService()->getEntryItemsOrderedByLikesForAdmin($entryId);
        }
        else
        {
            return Rockstar::$plugin->getService()->getEntryItemsOrderedByLikes($entryId, null);
        }
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

    public function getRankKeyLikeDescription($entryId, $rankerKey)
    {
        return Rockstar::$plugin->getService()->getRankKeyLikeDescription($entryId, $rankerKey);
    }

    public function getBulletinBoardPosts($limit = null, $liveOnly = true)
    {
        return Rockstar::$plugin->getBulletinService()->getBulletinBoardPosts($limit, $liveOnly);
    }

    public function currentUserBulletinPostExists()
    {
        return Rockstar::$plugin->getBulletinService()->currentUserBulletinPostExists();
    }

    public function getCurrentUserBulletinPost()
    {
        return Rockstar::$plugin->getBulletinService()->getCurrentUserBulletinPost();
    }

    public function getCurrentUserBulletinReplies()
    {
        return Rockstar::$plugin->getBulletinService()->getCurrentUserBulletinReplies();
    }

    public function countCurrentUserBulletinReplies()
    {
        return Rockstar::$plugin->getBulletinService()->countCurrentUserBulletinReplies();
    }
}