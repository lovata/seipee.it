<?php namespace Tecnotrade\Tools;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'Tools',
            'description' => 'Utility e PDF per Tecnotrade',
            'author'      => 'Tecnotrade',
            'icon'        => 'icon-wrench'
        ];
    }

    public function registerComponents() {}
    public function registerSettings() {}
}