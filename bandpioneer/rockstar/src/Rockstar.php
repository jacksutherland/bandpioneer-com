<?php
/**
 * Band Pioneer Craft CMS 4.x
 *
 * Band Pioneer plugin for base website design properties.
 *
 * @link      https://bandpioneer.com
 * @copyright Copyright (c) 2023 Band Pioneer
 */

namespace bandpioneer\rockstar;

use Craft;

use craft\web\UrlManager;
use craft\elements\Entry;
use craft\events\DefineHtmlEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\CraftVariable;

use bandpioneer\rockstar\services\AIService;
use bandpioneer\rockstar\services\KeywordService;
use bandpioneer\rockstar\services\RockstarService;
use bandpioneer\rockstar\variables\BandsVariable;

use yii\base\Event;

/**
 * Class Rockstar
 *
 * @author    Band Pioneer
 * @package   Rockstar
 * @since     1.0.0
 */
class Rockstar extends craft\base\Plugin
{

    // Static Properties
    // =========================================================================

    public static $plugin;


    // Public Properties
    // =========================================================================

    // public $schemaVersion = '1.0.0';

    public bool $hasCpSettings = false;

    public bool $hasCpSection = false;


    // Public Methods
    // =========================================================================

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        $this->registerComponents();

        $request = Craft::$app->getRequest();

        if ($request->getIsSiteRequest())
        {
            Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function(RegisterUrlRulesEvent $event)
            {
                $event->rules['bands/dashboard'] = 'rockstar/bands/dashboard';

                $event->rules['bands/dashboard/edit/<slug>'] = 'rockstar/bands/dashboard';
                $event->rules['bands/save-band'] = 'rockstar/bands/save-band';
                $event->rules['bands/save-epk-info'] = 'rockstar/bands/save-epk-info';
                $event->rules['bands/save-video'] = 'rockstar/bands/save-video';
                $event->rules['bands/save-song'] = 'rockstar/bands/save-song';
                $event->rules['bands/save-image'] = 'rockstar/bands/save-image';
                $event->rules['bands/save-keyword'] = 'rockstar/bands/save-keyword';
                $event->rules['bands/delete-video'] = 'rockstar/bands/delete-video';
                $event->rules['bands/delete-logo'] = 'rockstar/bands/delete-logo';
                $event->rules['bands/delete-image'] = 'rockstar/bands/delete-image';
                $event->rules['bands/delete-song'] = 'rockstar/bands/delete-song';
            });
        }

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event)
        {
            $event->sender->set('bands', BandsVariable::class);
        });

        Event::on(Entry::class, Entry::EVENT_DEFINE_SIDEBAR_HTML, static function (DefineHtmlEvent $event)
        {
                
            Craft::debug('Entry::EVENT_DEFINE_SIDEBAR_HTML', __METHOD__);

            $entry = $event->sender;
            $entrySlug = $entry->slug;

            $html = Rockstar::$plugin->getKeywordService()->getEntryCPHTML($entrySlug);

            $event->html .= $html;
        });
    }

    public function getService(): RockstarService
    {
        return $this->get('service');
    }

    public function getAIService(): AIService
    {
        return $this->get('aiService');
    }

    public function getKeywordService(): KeywordService
    {
        return $this->get('keywordService');
    }


    // Private Methods
    // =========================================================================

    private function registerComponents(): void
    {
        $this->setComponents([
            'service' => RockstarService::class,
            'aiService' => AIService::class,
            'keywordService' => KeywordService::class
        ]);
    }



    // Protected Methods
    // =========================================================================

    
}
