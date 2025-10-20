<?php namespace Tecnotrade\Blocks;

use Cms\Classes\Controller;
use October\Rain\Support\Facades\Event;
use OFFLINE\Boxes\Classes\Events;
use System\Classes\PluginBase;
use RatMD\BlogHub\Models\Tag as RatTag;
use RainLab\Blog\Models\Category as RainlabCategory;
use Offline\Boxes\Models\Box;

class Plugin extends PluginBase
{
    
    
    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }

    public function register()
    {
        \Event::listen(
            \OFFLINE\Boxes\Classes\Events::REGISTER_PARTIAL_PATH,
            fn () => ['$/plugins/tecnotrade/blocks/partials']
        );

        Event::listen('cms.page.init', function (Controller $controller) {
            
            //$controller->addCss('/plugins/tecnotrade/blocks/assets/owl-carousel/assets/owl.carousel.min.css');
            //$controller->addJs('/plugins/tecnotrade/blocks/assets/owl-carousel/owl.carousel.min.js');
            
            
            
            $controller->addCss('https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css', [
                'integrity' => 'sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor',
                'crossorigin' => 'anonymous',
            ]);
            $controller->addCss('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.3/font/bootstrap-icons.css');
            
            $controller->addJs('https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js', [
                'integrity' => 'sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2',
                'crossorigin' => 'anonymous',
                'async' => 'async',
            ]);
        });
    }
    public function getBoxBlogPostCategoryOptions()
    {
        return $this->buildCategoryTree();
    }


    private function buildCategoryTree($parentId = null, $indent = '')
    {
    $categories = RainlabCategory::where('parent_id', $parentId)
        ->orderBy('name', 'asc')
        ->get();

    $tree = [];

    if ($parentId === null) {
        $tree[''] = '-- Seleziona una categoria --';
    }

    if ($parentId === null) {
        $tree[''] = '-- Seleziona una categoria --';
    }

    foreach ($categories as $category) {
        $tree[$category->slug] = $indent . $category->name;
        $tree = array_merge($tree, $this->buildCategoryTree($category->id, $indent . '-- '));
    }

    return $tree;
    }

    private function getBlogTags(){
        $tags=[];
        $tags['']='-- Seleziona un tag --';
        $allTags = RatTag::get()->sortBy('title');
        foreach($allTags as $t){
            $tags[$t->slug]=$t->title;
        }

        return $tags;
    }


    public function boot(){
        Box::extend(function ($model) {
           
            $model->addDynamicMethod('getBoxBlogPostCategoryOptions', function () {
                return $this->buildCategoryTree();
            });
            $model->addDynamicMethod('getBoxBlogPostTagsOptions',function(){
                return $this->getBlogTags();
            });
        });
    }

}
