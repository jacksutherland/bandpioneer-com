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
// use bandpioneer\rockstar\services\RockstarService;
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
        $request = Craft::$app->getRequest();

        // Craft::$app->onInit(function() {
        //     // echo 'Made it to onInit';
        //     // die();
        // });

        if ($request->getIsSiteRequest())
        {
            // $this->controllerNamespace = 'bandpioneer\rockstar\controllers';

            // echo 'Made it here';
            // die();

            Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function(RegisterUrlRulesEvent $event)
            {
                $event->rules['bands/public'] = 'rockstar/bands/public';
                $event->rules['bands/private'] = 'rockstar/bands/private';
            });
        }
    }


    // Private Methods
    // =========================================================================


    // Protected Methods
    // =========================================================================

    
}
