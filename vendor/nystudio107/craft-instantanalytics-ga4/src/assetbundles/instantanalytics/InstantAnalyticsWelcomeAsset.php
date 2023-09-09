<?php
/**
 * Instant Analytics plugin for Craft CMS
 *
 * Instant Analytics brings full Google Analytics support to your Twig templates
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\instantanalyticsGa4\assetbundles\instantanalytics;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\vue\VueAsset;

/**
 * @author    nystudio107
 * @package   Instant Analytics
 * @since     1.0.0
 */
class InstantAnalyticsWelcomeAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->sourcePath = '@nystudio107/instantanalyticsGa4/web/assets/dist';

        $this->depends = [
            CpAsset::class,
            VueAsset::class,
            InstantAnalyticsAsset::class,
        ];

        parent::init();
    }
}
