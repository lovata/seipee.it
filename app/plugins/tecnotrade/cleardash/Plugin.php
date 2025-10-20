<?php namespace Tecnotrade\Cleardash;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'Cache Cleaner',
            'description' => 'Widget con pulsanti per cache:clear e october:optimize',
            'author'      => 'Tecnotrade',
            'icon'        => 'icon-dashboard'
        ];
    }

    public function registerReportWidgets()
    {
        return [
            'Tecnotrade\Cleardash\ReportWidgets\ClearCacheWidget' => [
                'label'   => 'Cache Cleaner',
                'context' => 'dashboard'
            ]
        ];
    }
}