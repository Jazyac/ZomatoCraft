<?php
/**
 * Zomato module for Craft CMS 3.x
 *
 * Module to add functionality for accessing Zomato API
 *
 * @link      example.com
 * @copyright Copyright (c) 2019 jazyac
 */
namespace modules\zomatomodule;
use modules\zomatomodule\assetbundles\zomatomodule\ZomatoModuleAsset;
use Craft;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\TemplateEvent;
use craft\i18n\PhpMessageSource;
use craft\web\View;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\Module;
/**
 * Class ZomatoModule
 *
 *
 */
class ZomatoModule extends Module
{
    // Static Properties
    // =========================================================================
    /**
     * @var ZomatoModule
     */
    public static $instance;
    // Public Methods
    // =========================================================================
    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
        Craft::setAlias('@modules/zomatomodule', $this->getBasePath());
        $this->controllerNamespace = 'modules\zomatomodule\controllers';
        // Translation category
        $i18n = Craft::$app->getI18n();
        /** @noinspection UnSafeIsSetOverArrayInspection */
        if (!isset($i18n->translations[$id]) && !isset($i18n->translations[$id.'*'])) {
            $i18n->translations[$id] = [
                'class' => PhpMessageSource::class,
                'sourceLanguage' => 'en-US',
                'basePath' => '@modules/zomatomodule/translations',
                'forceTranslation' => true,
                'allowOverrides' => true,
            ];
        }
        // Base template directory
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $e) {
            if (is_dir($baseDir = $this->getBasePath().DIRECTORY_SEPARATOR.'templates')) {
                $e->roots[$this->id] = $baseDir;
            }
        });
        Craft::$app->view->hook('Display-Restaurants', function(array &$context) {
        // create curl resource 
            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'user-key: 1a5ca52213f92365977524d7cda0c9cb',//should be an environment variable.
                'Accept: application/json'
            ));
        // set url 
            curl_setopt($ch, CURLOPT_URL, "https://developers.zomato.com/api/v2.1/search?entity_id=14&entity_type=city&count=20&collection_id=1"); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch); 
            if (curl_errno($ch)) {
                die('Couldn\'t send request: ' . curl_error($ch));
            } else {
                $resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($resultStatus != 200) {
                    die('Request failed: HTTP status code: ' . $resultStatus);
                }
                else{
                    $context['JSONrestaurant'] = $output;
                }
            }
        // close curl resource to free up system resources 
            curl_close($ch);  
        });
        // Set this as the global instance of this module class
        static::setInstance($this);
        parent::__construct($id, $parent, $config);
    }
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$instance = $this;
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            Event::on(
                View::class,
                View::EVENT_BEFORE_RENDER_TEMPLATE,
                function (TemplateEvent $event) {
                    try {
                        Craft::$app->getView()->registerAssetBundle(ZomatoModuleAsset::class);
                    } catch (InvalidConfigException $e) {
                        Craft::error(
                            'Error registering AssetBundle - '.$e->getMessage(),
                            __METHOD__
                        );
                    }
                }
            );
        }
        Craft::info(
            Craft::t(
                'zomato-module',
                '{name} module loaded',
                ['name' => 'Zomato']
            ),
            __METHOD__
        );
    }
    // Protected Methods
    // =========================================================================
}
