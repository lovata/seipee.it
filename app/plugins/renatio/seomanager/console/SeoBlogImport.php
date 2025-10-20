<?php

namespace Renatio\SeoManager\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use RainLab\Blog\Models\Category;
use RainLab\Blog\Models\Post;
use System\Classes\PluginManager;

class SeoBlogImport extends Command
{
    protected $signature = 'seo:import-blog';

    protected $description = 'Import Blog SEO';

    public function handle()
    {
        if (! PluginManager::instance()->exists('RainLab.Blog')) {
            return;
        }

        Artisan::call('seo:migrate-tables --table=rainlab_blog_posts');
        Artisan::call('seo:migrate-tables --table=rainlab_blog_categories');

        $this->importPosts();
        $this->importCategories();

        $this->info('Import run successfully.');
    }

    protected function importPosts()
    {
        Post::withoutEvents(function () {
            Post::all('id', 'title', 'excerpt')
                ->each(function ($post) {
                    if (empty($post->meta_title)) {
                        $post->meta_title = str_limit($post->title, 255);
                    }

                    if (empty($post->meta_description)) {
                        $post->meta_description = ! empty($post->excerpt)
                            ? str_limit($post->excerpt, 300)
                            : str_limit(strip_tags($post->content), 300);
                    }

                    $post->forceSave();
                });
        });
    }

    protected function importCategories()
    {
        Category::withoutEvents(function () {
            Category::all('id', 'name')
                ->each(function ($category) {
                    if (empty($category->meta_title)) {
                        $category->meta_title = str_limit($category->name, 255);
                    }

                    if (empty($category->meta_description)) {
                        $category->meta_description = str_limit($category->name, 300);
                    }

                    $category->forceSave();
                });
        });
    }
}
