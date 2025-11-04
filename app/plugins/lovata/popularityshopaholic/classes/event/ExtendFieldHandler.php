<?php namespace Lovata\PopularityShopaholic\Classes\Event;

use System\Classes\PluginManager;
use System\Controllers\Settings as SettingsController;

use Lovata\Shopaholic\Models\Product;
use Lovata\Shopaholic\Controllers\Products;
use Lovata\Shopaholic\Models\Settings as SettingsModel;

/**
 * Class ExtendFieldHandler
 * @package Lovata\PopularityShopaholic\Classes\Event
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ExtendFieldHandler
{
    /**
     * Add listeners
     * @param \Illuminate\Events\Dispatcher $obEvent
     */
    public function subscribe($obEvent)
    {
        $obEvent->listen('backend.form.extendFields', function ($obWidget) {
            $this->extendSettingsFields($obWidget);
            $this->extendProductFields($obWidget);
        });
    }

    /**
     * Extend Product fields
     * @param \Backend\Widgets\Form $obWidget
     */
    protected function extendSettingsFields($obWidget)
    {
        if (!$obWidget->getController() instanceof SettingsController || $obWidget->isNested) {
            return;
        }

        if (!$obWidget->model instanceof SettingsModel) {
            return;
        }

        $arFieldList = [
            'popularity_open_product' => [
                'label'   => 'lovata.popularityshopaholic::lang.field.popularity_open_product',
                'tab'     => 'lovata.popularityshopaholic::lang.tab.popularity_settings',
                'span'    => 'left',
                'default' => 1,
                'type'    => 'number',
            ],
        ];

        if (PluginManager::instance()->hasPlugin('Lovata.OrdersShopaholic')) {
            $arFieldList['popularity_cart_add'] = [
                'label'   => 'lovata.popularityshopaholic::lang.field.popularity_cart_add',
                'tab'     => 'lovata.popularityshopaholic::lang.tab.popularity_settings',
                'span'    => 'left',
                'default' => 2,
                'type'    => 'number',
            ];

            $arFieldList['popularity_order_create'] = [
                'label'   => 'lovata.popularityshopaholic::lang.field.popularity_order_create',
                'tab'     => 'lovata.popularityshopaholic::lang.tab.popularity_settings',
                'span'    => 'left',
                'default' => 5,
                'type'    => 'number',
            ];
        }

        $obWidget->addTabFields($arFieldList);
    }

    /**
     * Extend Product fields
     * @param \Backend\Widgets\Form $obWidget
     */
    protected function extendProductFields($obWidget)
    {
        if (!$obWidget->getController() instanceof Products || $obWidget->isNested || empty($obWidget->context)) {
            return;
        }

        if (!$obWidget->model instanceof Product) {
            return;
        }

        $arFieldList = [
            'popularity' => [
                'label'   => 'lovata.popularityshopaholic::lang.field.popularity',
                'tab'     => 'lovata.toolbox::lang.tab.settings',
                'span'    => 'left',
                'default' => 0,
                'type'    => 'number',
            ],
        ];

        $obWidget->addTabFields($arFieldList);
    }
}
