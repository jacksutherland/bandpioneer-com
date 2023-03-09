<?php
// namespace verbb\comments\variables;

// use verbb\comments\Comments;
// use verbb\comments\helpers\CommentsHelper;
// use verbb\comments\elements\db\CommentQuery;

namespace bandpioneer\rockstar\variables;

use bandpioneer\rockstar\Rockstar;

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
	public function getBand($slug)
    {
        return Rockstar::$plugin->getService()->getBandBySlug($slug);
    }
}