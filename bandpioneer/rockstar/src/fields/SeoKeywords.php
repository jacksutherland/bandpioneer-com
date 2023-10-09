<?php
/**
 * Band Pioneer Craft CMS 4.x
 *
 * Band Pioneer custom plugin
 *
 * @link      https://bandpioneer.com
 * @copyright Copyright (c) 2023 Band Pioneer
 */

namespace bandpioneer\rockstar\fields;

use bandpioneer\rockstar\Rockstar;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\FieldInterface;

class SeoKeywords extends Field implements FieldInterface
{
	public static function displayName(): string
    {
        return 'SEO Keywords';
    }

    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {
    	$keyword = str_replace("-", " ", $element->slug);

    	$service = Rockstar::$plugin->getKeywordService();

    	$keywordData = $service->getKeywordData($keyword);

    	$jsonData = json_decode($keywordData['data'], true);

    	$variables = [ 
    		'slug' => $element->slug, 
    		'keyword' => $keyword,
    		'data' => [
    			'isValid' => $keywordData['isValid'],
    			'error' => $keywordData['error'],
    			'vol' => $jsonData['data'][1]['vol']
    		]
    	];

    	return Craft::$app->getView()->renderTemplate(
            'rockstar/fields/seo_keywords',
            $variables
        );
    }
}