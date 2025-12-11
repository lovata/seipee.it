<?php namespace Lovata\BaseCode;

use Event;
use Lovata\Basecode\Classes\Event\User\UserItemModelHandler;
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
    /**
     * Register plugin components
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            '\Lovata\BaseCode\Components\UserChildrenPage' => 'UserChildrenPage',
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
        return [];
    }

    /**
     * Add listener
     */
    protected function addEventListener()
    {
        Event::subscribe(UserModelHandler::class);
        Event::subscribe(UserItemModelHandler::class);
    }
}
