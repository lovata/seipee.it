<?php namespace Lovata\PopularityShopaholic;

use Event;
use System\Classes\PluginBase;

use Lovata\PopularityShopaholic\Classes\Event\ExtendFieldHandler;
use Lovata\PopularityShopaholic\Classes\Event\PopularityHandler;
use Lovata\PopularityShopaholic\Classes\Event\ProductModelHandler;

/**
 * Class Plugin
 * @package Lovata\PopularityShopaholic
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class Plugin extends PluginBase
{
    /** @var array Plugin dependencies */
    public $require = ['Lovata.Shopaholic', 'Lovata.Toolbox'];

    /**
     * Plugin boot method
     */
    public function boot()
    {
        $this->addEventListener();
    }

    /**
     * Add event listeners
     */
    protected function addEventListener()
    {
        Event::subscribe(ExtendFieldHandler::class);
        Event::subscribe(PopularityHandler::class);
        Event::subscribe(ProductModelHandler::class);
    }
}
