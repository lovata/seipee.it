<?php

namespace Renatio\SeoManager\Console;

use Cms\Classes\Page;
use Illuminate\Console\Command;

class SeoCmsPageImport extends Command
{
    protected $signature = 'seo:import-cms';

    protected $description = 'Import CMS Pages SEO';

    public function handle()
    {
        foreach (Page::all() as $page) {
            $viewBag = $page->viewBag;

            $viewBag['seo_title'] = $viewBag['seo_title'] ?? $page->meta_title ?? $page->title;
            $viewBag['seo_description'] = $viewBag['seo_description'] ?? $page->meta_description ?? $page->title;
            $viewBag['robot_index'] = $viewBag['robot_index'] ?? 'index';
            $viewBag['robot_follow'] = $viewBag['robot_follow'] ?? 'follow';

            $data['settings'] = ['viewBag' => $viewBag];

            $page->fill($data);
            $page->forceSave();
        }

        $this->info('Import run successfully.');
    }
}
