<?php
/**
 * @link https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license https://craftcms.github.io/license/
 */

namespace craft\web\assets\updates;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\View;

/**
 * Asset bundle for the Updates utility
 */
class UpdatesAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = __DIR__ . '/dist';

    /**
     * @inheritdoc
     */
    public $depends = [
        CpAsset::class,
    ];

    /**
     * @inheritdoc
     */
    public $css = [
        'css/UpdatesUtility.css',
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'UpdatesUtility.js',
    ];

    /**
     * @inheritdoc
     */
    public function registerAssetFiles($view): void
    {
        parent::registerAssetFiles($view);

        if ($view instanceof View) {
            $view->registerTranslations('app', [
                'Copy package name',
                'Copy plugin handle',
                'Critical',
                'Package Name',
                'Plugin Handle',
                'Unable to fetch updates at this time.',
                'Update all',
                'You’re all up to date!',
            ]);
        }
    }
}
