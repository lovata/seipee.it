<?php

namespace Renatio\SeoManager\Classes;

use RainLab\Blog\Models\Category;
use RainLab\Blog\Models\Post;
use Renatio\SeoManager\Behaviors\SeoModel;
use Schema;
use System\Classes\PluginManager;

class SeoBlog
{
    public function extend()
    {
        if (! PluginManager::instance()->exists('RainLab.Blog')) {
            return;
        }

        $this->extendModels();

        $this->saveDefaultValues();
    }

    protected function extendModels()
    {
        $this->extendPostModel();

        $this->extendCategoryModel();
    }

    protected function extendPostModel()
    {
        Post::extend(function ($model) {
            $model->implement[] = SeoModel::class;

            $model->addDynamicMethod('getSeoTab', function () {
                return 'secondary';
            });
        });
    }

    protected function extendCategoryModel()
    {
        Category::extend(function ($model) {
            $model->implement[] = SeoModel::class;
        });
    }

    protected function saveDefaultValues()
    {
        $this->saveDefaultValuesForPost();

        $this->saveDefaultValuesForCategory();
    }

    protected function saveDefaultValuesForPost()
    {
        Post::extend(function ($model) {
            $model->bindEvent('model.beforeSave', function () use ($model) {
                if (! Schema::hasColumns('rainlab_blog_posts', ['meta_title', 'meta_description'])) {
                    return;
                }

                if (empty($model->meta_title)) {
                    $model->meta_title = str_limit($model->title, 255);
                }

                if (empty($model->meta_description)) {
                    $model->meta_description = ! empty($model->excerpt)
                        ? str_limit($model->excerpt, 300)
                        : str_limit(strip_tags($model->content), 300);
                }
            });
        });
    }

    protected function saveDefaultValuesForCategory()
    {
        Category::extend(function ($model) {
            $model->bindEvent('model.beforeSave', function () use ($model) {
                if (! Schema::hasColumns('rainlab_blog_categories', ['meta_title', 'meta_description'])) {
                    return;
                }

                if (empty($model->meta_title)) {
                    $model->meta_title = str_limit($model->name, 255);
                }

                if (empty($model->meta_description)) {
                    $model->meta_description = str_limit($model->name, 300);
                }
            });
        });
    }
}
