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

		$qty = round($total / 3);
		$remainder = $total - round($total / 2);

		$query = "Our primary keyword is: ${keyword}. I need ${total} long-tail keywords related to it that a lot of people are searching for. ${qty} of them should be closely related to the primary SEO keyword, ${qty} should be moderately related to it, and the remaining ${remainder} should just barely be related to it. Delimit each keyword by commas only. No numbers or bullets, comma delimited keywords only.";
	   
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

			$keywordCount = 45;

			$aiResponse = $this->getKeywordAIResponse($keyword, $keywordCount);
	    	$keywordArray = explode(",", $aiResponse);
	    	$response = $this->keywordAPICall($keywordArray);

			$data = $response['data'];
			$error = $response['error'];
			$info = $response['info'];

			$jsonData = json_decode($data, true);

			$volData = [];

			foreach ($jsonData["data"] as $item)
			{
				if (isset($item['vol']) && $item['vol'] > 0)
				{
				    array_push($volData, $item);
				}
			}

			if (!empty($volData))
			{
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

		// $jsonData = json_encode($relatedKeywords["data"]);

		foreach ($data["data"] as $item)
		{
			$html .= "<p><strong>Keyword:</strong> {$item['keyword']}, <strong>Volume:</strong> {$item['vol']}, <strong>Competition:</strong> {$item['competition']}, <strong>CPC:</strong> {$item['cpc']['value']}</p>";
		}

		$html .= "</div>";

		return $html;
	}
}



































