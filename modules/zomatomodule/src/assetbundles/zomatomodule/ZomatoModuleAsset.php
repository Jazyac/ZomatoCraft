<?php
/**
 * Zomato module for Craft CMS 3.x
 *
 * Module to add functionality for accessing Zomato API
 *
 * @link      example.com
 * @copyright Copyright (c) 2019 jazyac
 */

namespace modules\zomatomodule\assetbundles\ZomatoModule;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    jazyac
 * @package   ZomatoModule
 * @since     1.0.0
 */
class ZomatoModuleAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@modules/zomatomodule/assetbundles/zomatomodule/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/ZomatoModule.js',
        ];

        $this->css = [
            'css/ZomatoModule.css',
        ];

        parent::init();
    }
}
