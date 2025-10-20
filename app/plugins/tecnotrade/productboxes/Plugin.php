<?php namespace Tecnotrade\Productboxes;

use System\Classes\PluginBase;
use OFFLINE\Mall\Models\Product;

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
    }

    /**
     * registerSettings used by the backend.
     */
    public function registerSettings()
    {
    }

    public function registerMarkupTags()
    {
        return [
            'functions' => [
                'getProductByUserDefinedId' => function ($userDefinedId) {
                    return Product::with('image_sets.images')
                        ->where('user_defined_id', $userDefinedId)
                        ->first();
                },
            ],
        ];
    }
    


}
