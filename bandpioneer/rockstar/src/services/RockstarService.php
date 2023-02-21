<?php
/**
 * Band Pioneer Craft CMS 4.x
 *
 * Band Pioneer plugin for base website design properties.
 *
 * @link      https://bandpioneer.com
 * @copyright Copyright (c) 2023 Band Pioneer
 */

namespace bandpioneer\rockstar\services;

use Craft;
use craft\gql\base\ObjectType;
use yii\base\Component;
use craft\helpers\DateTimeHelper;
use craft\helpers\StringHelper;
use craft\helpers\Db;

use bandpioneer\rockstar\Rockstar;

/**
 * @author    Band Pioneer
 * @package   Rockstar
 */
class RockstarService extends Component
{
    public function test()
    {
        $str = 'Here is a test string';

        return $str;
    }

}
