<?php namespace Tecnotrade\TecnotradeGallery\Components;

use Cms\Classes\ComponentBase;
use Tecnotrade\Tecnotradegallery\Models\Gallery;

class GalleryDisplay extends ComponentBase
{
    public $gallery;

    public function componentDetails()
    {
        return [
            'name' => 'Gallery Display',
            'description' => 'Displays a specific gallery based on the slug selected in the snippet.',
            'snippetAjax' => true
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title' => 'Select Gallery',
                'description' => 'Select the gallery to display by slug.',
                'type' => 'dropdown',
            ],
            'width'=>[
                'title' => 'Seleziona larghezza foto',
                'description' => 'definisci quante foto desideri vedere su una riga',
                'type' => 'dropdown',
                'default' => '12',
                'options' => [
                    'col-lg-1' => '1 Colonna',
                    'col-lg-2' => '2 Colonne',
                    'col-lg-3' => '3 Colonne',
                    'col-lg-4' => '4 Colonne',
                    'col-lg-5' => '5 Colonne',
                    'col-lg-6' => '6 Colonne',
                    'col-lg-7' => '7 Colonne',
                    'col-lg-8' => '8 Colonne',
                    'col-lg-9' => '9 Colonne',
                    'col-lg-10' => '10 Colonne',
                    'col-lg-11' => '11 Colonne',
                    'col-lg-12' => '12 Colonne'
                ],
            ],
            'enableJustifiedGallery' => [
                'title' => 'Enable Justified Gallery',
                'description' => 'Add the justifiedGallery class to the gallery.',
                'type' => 'checkbox',
                'default' => false
            ]
        ];
    }

    public function getSlugOptions()
    {
        return Gallery::all()->pluck('slug', 'slug')->toArray();
    }

    public function onRun()
    {
        //$this->addCss('/plugins/tecnotrade/tecnotradegallery/assets/css/lightgallery-core.css');
        //$this->addCss('/plugins/tecnotrade/tecnotradegallery/assets/css/lightgallery-bundle.min.css');
        $this->addCss('https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.1/css/lightgallery-bundle.min.css');
        $this->addCss('/plugins/tecnotrade/tecnotradegallery/assets/css/justifiedGallery.min.css');
        $this->addJs('/plugins/tecnotrade/tecnotradegallery/assets/js/lightgallery.min.js');
        $this->addJs('/plugins/tecnotrade/tecnotradegallery/assets/js/justifiedGallery.min.js');
        $this->addJs('/plugins/tecnotrade/tecnotradegallery/assets/js/app.js');
        // Carica solo la galleria con lo slug specificato
        $this->page['gallery'] = $this->loadGallery();

    }

    protected function loadGallery()
    {
        $slug = $this->property('slug');
        return Gallery::where('slug', $slug)->first();
    }
}
