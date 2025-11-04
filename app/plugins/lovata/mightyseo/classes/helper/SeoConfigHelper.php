<?php namespace Lovata\MightySeo\Classes\Helper;

use System\Traits\ConfigMaker;
use October\Rain\Support\Traits\Singleton;

use Lovata\Toolbox\Traits\Helpers\TraitInitActiveLang;

/**
 * Class SeoConfigHelper
 * @package Lovata\MightySeo\Classes\Helper
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class SeoConfigHelper
{
    use Singleton;
    use ConfigMaker;
    use TraitInitActiveLang;

    protected $arModelClassList = [];
    protected $arItemClassList = [];
    protected static $arDefaultSeoModels = [
        \Tailor\Models\EntryRecord::class,
        \Tailor\Models\GlobalRecord::class,
        [
            'model' => 'RainLab\Blog\Models\Post',
            'tab'   => 'secondary',
        ],
        'Lovata\Shopaholic\Models\Brand',
        'Lovata\Shopaholic\Models\Category',
        'Lovata\Shopaholic\Models\Product',
        'Lovata\Shopaholic\Models\PromoBlock',
        'Lovata\TagsShopaholic\Models\Tag',
        'Lovata\GoodNews\Models\Article',
        'Lovata\GoodNews\Models\Category',
    ];

    protected static $arActiveLangList = null;

    /**
     * Get model class list form config
     * @return array
     */
    public static function getModelClassList()
    {
        return self::instance()->arModelClassList;
    }
    /**
     * Get item class list form config
     * @return array
     */
    public static function getItemClassList()
    {
        return self::instance()->arItemClassList;
    }

    public static function getSeoModelsConfig(): array
    {
        return array_merge(self::$arDefaultSeoModels, (array) config('app.seo_models'));
    }

    /**
     * Init config data
     */
    protected function init()
    {
        $this->initModelClassList();
        $this->initItemClassList();

        $this->initActiveLang();
    }

    /**
     * Init model class list from config
     */
    protected function initModelClassList()
    {
        //Get model list form config
        /** @var array $arConfigList */
        $arConfigList = self::getSeoModelsConfig();
        if (empty($arConfigList)) {
            return;
        }

        //Process config list
        foreach ($arConfigList as $mConfig) {
            $sClassName = is_array($mConfig) ? $mConfig['model'] : $mConfig;
            if (empty($sClassName) || !class_exists($sClassName)) {
                continue;
            }

            $this->arModelClassList[] = $sClassName;
        }
    }

    /**
     * Init item class list form config
     */
    protected function initItemClassList()
    {
        //Get model list form config
        /** @var array $arConfigList */
        $arConfigList = config('app.seo_items');
        if (empty($arConfigList)) {
            return;
        }

        //Process config list
        foreach ($arConfigList as $sClassName) {
            if (empty($sClassName) || !class_exists($sClassName)) {
                continue;
            }

            $this->arItemClassList[] = $sClassName;
        }
    }
}
