<?php namespace Lovata\BaseCode;

use Event;
use Lang;
use Lovata\Basecode\Classes\Event\Offer\OfferModelHandler;
use Lovata\Basecode\Classes\Event\Order\OrderModelHandler;
use Lovata\Basecode\Classes\Event\Product\ProductModelHandler;
use Lovata\Basecode\Classes\Event\User\UserModelHandler;
use System\Classes\PluginBase;
//Console commands
use Lovata\BaseCode\Classes\Console\ResetAdminPassword;

/**
 * Class Plugin
 *
 * @package Lovata\BaseCode
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class Plugin extends PluginBase
{

    public $require = [
        'Lovata.Buddies',
        'Lovata.OrdersShopaholic',
    ];

    /**
     * Register plugin components
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            '\Lovata\BaseCode\Components\UserChildrenPage' => 'UserChildrenPage',
            '\Lovata\BaseCode\Components\OrderPageCustom' => 'OrderPageCustom',
        ];
    }

    /**
     * Plugin boot method
     */
    public function boot()
    {
        $this->addEventListener();
    }

    public function register()
    {
        $this->registerConsoleCommand('basecode:reset_admin_password', ResetAdminPassword::class);
    }

    /**
     * @return array
     */
    public function registerMailTemplates()
    {
        return [
            'lovata.basecode::mail.create_order_manager' => Lang::get('lovata.basecode::mail.create_order_manager'),
            'lovata.basecode::mail.create_order_user'    => Lang::get('lovata.basecode::mail.create_order_manager'),
        ];
    }

    /**
     * Add listener
     */
    protected function addEventListener()
    {
        Event::subscribe(UserModelHandler::class);
        Event::subscribe(OfferModelHandler::class);
        Event::subscribe(OrderModelHandler::class);
        Event::subscribe(ProductModelHandler::class);
    }
}
