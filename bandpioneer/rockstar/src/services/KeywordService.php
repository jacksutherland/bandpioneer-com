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

use bandpioneer\rockstar\Rockstar;
use bandpioneer\rockstar\queue\jobs\PopulateEntries;

use yii\base\Component;

use Craft;
use craft\base\Element;
use craft\elements\Entry;
use craft\gql\base\ObjectType;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use craft\helpers\ElementHelper;

/**
 * @author    Band Pioneer
 * @package   KeywordService
 */

class AIQueries {
    const KEYWORDS = 1;
}

class KeywordService extends Component
{
	const NUMBER_OF_RELATED_KEYWORDS = 30;

	public static function getAIQuery($aiQueryType, $params = [])
	{
	    switch ($aiQueryType)
	    {
	        case AIQueries::KEYWORDS:
	        	$total = self::NUMBER_OF_RELATED_KEYWORDS;
	        	$qty = round($total / 3);
				$remainder = $total - round(2 * $qty);
		        return "Create {$total} long-tail SEO keywords that are common search terms, and related to the primary keyword: {$params['keyword']}. {$qty} of them should be similar to the primary keyword, {$qty} should just be a similar top to the primary keyword, and the remaining {$remainder} can me just remotely related to it. Each keyword should be less than 50 characters and separated by a comma. Do NOT add numbers or bullets! The returned keywords should be words and spaces only, and delimited by commas.";
	        default:
	        	return "";
	    }
	}

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

	public function getKeywordDataHtml($keyword)
	{
		$data = $this->getKeywordData($keyword);

		if ($data['isValid'])
		{
			$html = '<p><strong>Volume:</strong> ' . $data['vol'] . '</p>
				<p><strong>Competition:</strong> ' . $data['competition'] . '</p>
				<p><strong>Cost per Click:</strong> ' . $data['cpc'] . '</p>
				<p><strong>Credits Left:</strong> ' . $data['credits'] . '</p>
				<p><i>Strong keywords have high volume with low competition and cpc<br>(preferably less than 0.1).</i></p>
				<br>';
		}
		else
		{
			$html = '<p>' . $data->error . '</p><br>';
		}

		return $html;
	}

	public function keywordAPICall($keywordArray)
	{
		$apiKey = getenv('KE_API_KEY');

		$ch = curl_init('https://api.keywordseverywhere.com/v1/get_keyword_data');

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		  'Accept: application/json',
		  'Authorization: Bearer ' . $apiKey
		));

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,
		  urldecode(http_build_query([
		    "dataSource" => "gkp",
		    // "dataSource" => "cli",
		    // "country" => "us", // not provided global data is returned
		    // "currency" => "USD",
		    "kw" => $keywordArray
		  ]))
		);

		curl_setopt($ch, CURLOPT_FAILONERROR, true);

		$data = curl_exec($ch);
		$error = curl_error($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);

		return [
			'info' => $info,
			'data' => $data,
			'error' => $error,
		];
	}

	public function getKeywordData($keyword)
	{
		try
		{
			$response = $this->keywordAPICall([$keyword]);

			$data = $response['data'];
			$error = $response['error'];
			$info = $response['info'];

			$jsonData = json_decode($data, true);

			$credits = $jsonData['credits'];
			$volume = $jsonData['data'][0]['vol'];
			$cpcVal = $jsonData['data'][0]['cpc']['value'];
			$cpc = $jsonData['data'][0]['cpc']['currency'] . $cpcVal;
			$competition = $jsonData['data'][0]['competition'];
			// $competitionPct = round($competition * 100) . '%';

			return [ 
	    		'isValid' => ($info['http_code'] == 200),
	    		'error' => $error,
	    		'vol' => $volume,
	    		'cpc' => $cpc,
	    		'competition' => $competition,
	    		'credits' => $credits
	    	];
	    }
	    catch (Exception $e)
	    {
	    	return $this->invalidData($e->getMessage());
		}
	}

	private function getKeywordAIResponse($keyword, $total)
	{
		$aiService = Rockstar::$plugin->getAIService();

		$query = self::getAIQuery(AIQueries::KEYWORDS, ['keyword' => $keyword]);

		$aiResponse = $aiService->chatQuery($query);

	    return $aiResponse;
	}

	private function invalidData($error)
	{
		return [ 
    		'isValid' => false,
    		'data' => [],
    		'error' => $error
    	];
	}

	public function getRelatedKeywords($keyword)
	{
		try {

			$aiResponse = $this->getKeywordAIResponse($keyword, self::NUMBER_OF_RELATED_KEYWORDS);

	    	$keywordArray = explode(",", $aiResponse);
	    	$response = $this->keywordAPICall($keywordArray);

			$data = $response['data'];
			$error = $response['error'];
			$info = $response['info'];

			$jsonData = json_decode($data, true);

			$volData = [];

			// only include keywords with volume greater than 0

			foreach ($jsonData["data"] as $item)
			{
				if (isset($item['vol']) && $item['vol'] > 0)
				{
				    array_push($volData, $item);
				}
			}

			if (count($volData) < 2)
			{
				// if there are no keywords with volume, return the top 5

				$volData = array_slice($jsonData["data"], 0, 6);
			}
			elseif (!empty($volData))
			{
				// order keywords by volume

				usort($volData, function($a, $b)
				{
				    return $b['vol'] - $a['vol'];
				});
			}

			return [ 
	    		'isValid' => ($info['http_code'] == 200),
	    		'error' => $error,
	    		'data' => $volData
	    	];
		 }
	    catch (Exception $e)
	    {
			return $this->invalidData($e->getMessage());
		}
	}

	public function getRelatedKeywordHtml($keyword)
	{
		$data = $this->getRelatedKeywords($keyword);

		$html = "<div>";

		// create html for keyword data

		foreach ($data["data"] as $item)
		{
			if($item['keyword'] !== $keyword)
			{
				$html .= "<p><label><input type=\"checkbox\" value=\"{$item['keyword']}\"><strong>Keyword:</strong> {$item['keyword']}, <strong>Volume:</strong> {$item['vol']}, <strong>Competition:</strong> {$item['competition']}, <strong>CPC:</strong> {$item['cpc']['value']}</label></p>";
			}
		}

		$html .= "</div>";

		return $html;
	}

	public function createEntries($keywords, $category) : bool
	{
		$success = true;

		foreach ($keywords as $keyword)
		{
	        Craft::$app->getQueue()->push(new PopulateEntries([
                'keyword' => $keyword,
                'category' => $category
            ]));
		}

		return $success;
	}
}



































