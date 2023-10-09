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
        				<div class="field">
        					<div class="input" style="width:100%">
        						<input class="text fullwidth" type="text" value="' . $keyword . '">
        					</div>
        				</div>
    				</div>
				</fieldset>';
	}

	public function getKeywordData($keyword)
	{
		// echo 'getKeywordData: ' . $keyword;

		try
		{
			$ch = curl_init('https://api.keywordseverywhere.com/v1/get_keyword_data');

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			  'Accept: application/json',
			  'Authorization: Bearer 24e281111ff95e6ad674'
			));

			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,
			  urldecode(http_build_query([
			    "dataSource" => "gkp",
			    "country" => "us",
			    "currency" => "USD",
			    "kw" => [
			      "keywords tool",
			      "keyword planner",
			      ]
			  ]))
			);

			$data = curl_exec($ch);
			$err = curl_error($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);

			// var_dump($data);
			// exit();

			return [ 
	    		'isValid' => ($info['http_code'] == 200),
	    		'data' => $data,
	    		'error' => $err
	    	];
	    }
	    catch (Exception $e)
	    {
			return [ 
	    		'isValid' => false,
	    		'data' => [],
	    		'error' => $e->getMessage()
	    	];
		}

	}
}