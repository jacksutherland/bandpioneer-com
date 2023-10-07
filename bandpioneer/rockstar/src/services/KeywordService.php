<?php
/**
 * Band Pioneer Craft CMS 4.x
 *
 * Band Pioneer custom plugin
 *
 * @link      https://bandpioneer.com
 * @copyright Copyright (c) 2023 Band Pioneer
 */

namespace bandpioneer\rockstar\services;

use Craft;
use craft\gql\base\ObjectType;
use yii\base\Component;

use craft\helpers\DateTimeHelper;
use craft\helpers\Db;

/**
 * @author    Band Pioneer
 * @package   KeywordService
 */
class KeywordService extends Component
{
	public function getEntryCPHTML($entrySlug)
	{
		$keyword = str_replace("-", " ", $entrySlug);

		return '<fieldset>
    				<legend class="h6">Keywords</legend>
    				<div class="meta">
        				<div class="field">'
            				. $keyword .
        				'</div>
    				</div>
				</fieldset>';
	}
}