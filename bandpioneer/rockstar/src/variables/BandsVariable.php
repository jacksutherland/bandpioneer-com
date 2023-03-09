<?php
// namespace verbb\comments\variables;

// use verbb\comments\Comments;
// use verbb\comments\helpers\CommentsHelper;
// use verbb\comments\elements\db\CommentQuery;

namespace bandpioneer\rockstar\variables;

use bandpioneer\rockstar\Rockstar;
// use bandpioneer\rockstar\models\EpkModel as EpkModel;

use Craft;
use craft\elements\Asset;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Html;
use craft\helpers\Json;
use craft\helpers\Template;

// use  bandpioneer\Rockstar;

use Twig\Markup;

class BandsVariable
{
	public function getEpk($slug)
    {
    	return Rockstar::$plugin->getService()->getEpkBySlug($slug);
    }
}