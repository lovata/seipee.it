<?php namespace Tecnotrade\Ratmdmetatag;

use System\Classes\PluginBase;
use RatMD\BlogHub\Models\Tag;
use RatMD\BlogHub\Controllers\Tags;
use Tecnotrade\Ratmdmetatag\Classes\SEOFieldsProvider;

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
        // Estensione del modello Tag
        Tag::extend(function ($model) {
            $model->addFillable([
                'meta_title', 'meta_description', 'meta_keywords', 'canonical_url',
                'meta_robots', 'og_title', 'og_description', 'og_image',
                'og_url', 'og_type',
            ]);
        });

        // Estensione dei campi del form nel backend
        Tags::extendFormFields(function ($form, $model, $context) {
            if (!$model instanceof Tag) {
                return;
            }

            $form->addTabFields(SEOFieldsProvider::getSEOFields());
        });
    }

    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
        return [
            'Tecnotrade\Ratmdmetatag\Components\RatMdMetaTag' => 'ratMdMetaTag',
            
        ];
    }

    /**
     * registerSettings used by the backend.
     */
    public function registerSettings()
    {
    }
}
