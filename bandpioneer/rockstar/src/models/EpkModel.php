<?php
/**
 * Band Pioneer Craft CMS 4.x
 *
 * @link      https://bandpioneer.com
 * @copyright Copyright (c) 2023 Band Pioneer
 */

namespace bandpioneer\rockstar\models;

use Craft;
use craft\base\Model;
use craft\elements\Asset;

use bandpioneer\rockstar\records\BandRecord;
use bandpioneer\rockstar\records\EpkRecord;

class EpkModel extends Model
{
    const EPK_PATH = '/epk/';

    /*** PUBLIC URL PROPERTIES ***/

    private ?BandRecord $bandRecord = null;
    private ?EpkRecord $epkRecord = null;

    public bool $enabled = false;
    public ?string $slug = null;
    public ?string $bandName = null;
    public ?string $phone = null;
    public ?string $email = null;
    public ?string $websiteUrl = null;
    public Asset|null|false $logo = null;
    public Array $genres = [];
    public ?string $cta = null;
    public ?string $bio = null;
    public ?string $requirements = null;

    /*** ADDITIONAL PROPERTIES ***/

    public $social = [];
    public $videos = [];
    public $images = [];
    public $insurance = [
        'amount' => '',
        'description' => ''
    ];
    public $price = [
        'min' => '',
        'max' => ''
    ];
    public $length = [
        'min' => '',
        'max' => ''
    ];

    public function __construct($bandRecord, $epkRecord, $forceLoadAll = false)
    {
        if($bandRecord && $epkRecord && ($forceLoadAll || $epkRecord->enabled))
        {
            $this->bandRecord = $bandRecord;
            $this->epkRecord = $epkRecord;

            $this->bandName = $bandRecord->name;
            $this->phone = $bandRecord->phone;
            $this->email = $bandRecord->email;
            $this->websiteUrl = $bandRecord->websiteUrl;
            $this->logo = $bandRecord->logoId == null ? null : (Craft::$app->getAssets()->getAssetById($bandRecord->logoId) ?? null);
            
            $this->enabled = $epkRecord->enabled;
            $this->slug = $epkRecord->slug;
            $this->cta = $epkRecord->cta;
            $this->bio = $epkRecord->bio;
            $this->requirements = $epkRecord->requirements;

            if($forceLoadAll)
            {
                $this->loadAdditionalProperties();
            }
        }
    }

    public function url()
    {
        if($this->enabled && strlen(trim($this->slug)) > 0)
        {
            return self::EPK_PATH . $this->slug;
        }
        else
        {
            return null;
        }
    }

    private function loadAdditionalProperties()
    {
        if($this->epkRecord)
        {
            $this->insurance = json_decode($this->epkRecord->insurance, false) ?? $this->insurance;
            $this->price = json_decode($this->epkRecord->priceRange, false) ?? $this->price;
            $this->length = json_decode($this->epkRecord->gigLength, false) ?? $this->length;
            $this->social = json_decode($this->epkRecord->socialMedia, false) ?? [];

            if($this->epkRecord->videos)
            {
                $epkVideos = json_decode($this->epkRecord->videos, false);
                foreach($epkVideos as &$jsonVideo)
                {
                    array_push($this->videos, json_decode($jsonVideo));
                }
            }

            if($this->epkRecord->images)
            {
                $epkImages = json_decode($this->epkRecord->images, false);
                foreach($epkImages as &$jsonImg)
                {
                    $img = json_decode($jsonImg);
                    $img->image = Craft::$app->getAssets()->getAssetById(json_decode($jsonImg)->id) ?? false;
                    if($img->image)
                    {
                        array_push($this->images, $img);
                    }
                }
            }
        }
    }
}
