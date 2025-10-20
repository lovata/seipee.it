<?php namespace Tecnotrade\Blogmultisite;

use System\Classes\PluginBase;
use RainLab\Blog\Models\Post as PostModel;
use RainLab\Blog\Controllers\Posts as PostController;
use Cms\Classes\Theme;
use System\Models\Parameter;
use Event;
use Backend\Facades\BackendAuth;
use Session;
use Illuminate\Foundation\Application;
use Backend\Classes\Controller;



class Plugin extends PluginBase
{
    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }
    
    

    

   
    private $siteId;

   
    protected static function getSiteId(){
        $activeTheme = Theme::getActiveTheme();
        $currentSiteId = $activeTheme->getCustomData('site');
        $siteId=$currentSiteId->id;
        return $siteId;
    }

    protected static function extendPostListQuery(){
        Event::listen('backend.list.extendQuery', function ($listWidget, $query) {
            // Verifica se la query lavora sul modello Post
            if (method_exists($query, 'getModel') && $query->getModel() instanceof \RainLab\Blog\Models\Post) {
                $siteId = self::getSiteId();
                $query->where('site_id', $siteId);
            }
        });
    }

    protected static function extendPostModel(){
        PostModel::extend(function ($model) {
            $model->addDynamicMethod('scopeSite', function ($query) {
                $siteId = self::getSiteId();
                if ($siteId) {
                    $query->where('site_id', '=', $siteId);
                }
            });
        });
        PostModel::extend(function ($model) {
           
            $model->bindEvent('model.beforeCreate', function () use ($model) {
                  
                $siteId = $model->site_id ?? self::getSiteId();
                $model->site_id = $siteId;
            });
        });
    }

    protected static function extendPostController(){
        PostController::extendFormFields(function($form, $model, $context) {
            if (!$model instanceof PostModel) {
                return;
            }

            $form->addFields([
                'site_id' => [
                    'label' => 'ID Sito',
                    'type' => 'number',
                    'tab' => 'RainLab.Blog::lang.post.tab_manage',
                    'span' => 'left',
                    'hidden'=>true,
                ]
            ]);
        });

        PostController::extendListColumns(function($list, $model){
            $list->addColumns([
                'site_id' => [
                    'label' => 'ID Sito',
                    'type' => 'number',
                    'sortable' => true,
                    'invisible' => false
                ]
            ]);
        });
    }


    protected static function addMediaFinderToPost(){
        Event::listen('backend.form.extendFields', function (\Backend\Widgets\Form $formWidget) {
            if (!$formWidget->getController() instanceof PostController) {
                return;
            }
            if (!$formWidget->model instanceof PostModel) {
                return;
            }
            $formWidget->addSecondaryTabFields([
                'metadata[principal_image]' => [
                    'tab' => 'rainlab.blog::lang.post.tab_manage',
                    'label'   => 'Immagine Principale',
                    'type' => 'mediafinder',
                    'mode' => 'image'
                ],
            ]);
            $formWidget->removeField('featured_images');
        });
    }

    public function boot()
    {
        
        self::extendPostListQuery();
        self::extendPostModel();
        self::extendPostController();
        self::addMediaFinderToPost();
        if(!$this->app->runningInBackend()) {
            $translator = \RainLab\Translate\Classes\Translator::instance();
            if(!Session::get(\RainLab\Translate\Classes\Translator::SESSION_LOCALE)){
                $translator->setLocale('en-gb',TRUE);
            }
        }
        
    }
}

