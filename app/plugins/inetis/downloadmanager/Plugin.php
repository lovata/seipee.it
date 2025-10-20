<?php namespace Inetis\DownloadManager;

use Backend;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
        return [
            Components\Browser::class  => 'downloadManagerBrowser',
            Components\Password::class => 'downloadManagerPasswordForm',
        ];
    }

    public function registerPageSnippets()
    {
        return [
            Components\Browser::class  => 'downloadManagerBrowser',
            Components\Password::class => 'downloadManagerPasswordForm',
        ];
    }

}
