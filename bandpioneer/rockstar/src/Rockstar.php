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

// use craft\events\RegisterComponentTypesEvent;
// use craft\services\Fields;
// use craft\web\twig\variables\CraftVariable;
// use bandpioneer\rockstar\models\Settings;
use Craft;

// use craft\services\Plugins;
// use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;

// use bandpioneer\rockstar\controllers\RockstarController;
use bandpioneer\rockstar\services\RockstarService;
// use bandpioneer\rockstar\variables\RockstarVariable;
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
                // $event->rules['bands/public'] = 'rockstar/bands/public';
                // $event->rules['bands/private'] = 'rockstar/bands/private';

                $event->rules['bands/dashboard'] = 'rockstar/bands/dashboard';
                $event->rules['bands/save-band'] = 'rockstar/bands/save-band';
                $event->rules['bands/save-video'] = 'rockstar/bands/save-video';
                $event->rules['bands/delete-video'] = 'rockstar/bands/delete-video';
                $event->rules['bands/delete-logo'] = 'rockstar/bands/delete-logo';
            });
        }
    }

    public function getService(): RockstarService
    {
        return $this->get('service');
    }


    // Private Methods
    // =========================================================================

    private function registerComponents(): void
    {
        $this->setComponents([
            'service' => RockstarService::class
        ]);
    }



    // Protected Methods
    // =========================================================================

    
}
