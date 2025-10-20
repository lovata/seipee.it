<?php namespace Tecnotrade\Tecnotradegallery;

use System\Classes\PluginBase;

/**
 * Plugin class
 */
class Plugin extends PluginBase
{
    /**
     * register method, called when the plugin is first registered.
     */
    public function register()
    {
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot()
    {
    }

    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
        return [
            'Tecnotrade\TecnotradeGallery\Components\GalleryDisplay' => 'galleryDisplay'
            
        ];
    }

    public function registerPageSnippets()
    {
        return [
            'Tecnotrade\TecnotradeGallery\Components\GalleryDisplay' => 'galleryDisplay'
        ];
    }

    /**
     * registerSettings used by the backend.
     */
    public function registerSettings()
    {
    }
}
