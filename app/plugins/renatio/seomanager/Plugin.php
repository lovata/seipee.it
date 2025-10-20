<?php

namespace Renatio\SeoManager;

use Renatio\SeoManager\Classes\SeoBlog;
use Renatio\SeoManager\Classes\SeoCmsPage;
use Renatio\SeoManager\Classes\SeoStaticPage;
use Renatio\SeoManager\Components\SeoTags;
use Renatio\SeoManager\Console\MigrateTables;
use Renatio\SeoManager\Console\Patch;
use Renatio\SeoManager\Console\SeoBlogImport;
use Renatio\SeoManager\Console\SeoCmsPageImport;
use Renatio\SeoManager\Console\SeoStaticPageImport;
use Renatio\SeoManager\Models\Settings;
use System\Classes\PluginBase;
use Tailor\Controllers\Entries;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name' => 'renatio.seomanager::lang.plugin.name',
            'description' => 'renatio.seomanager::lang.plugin.description',
            'author' => 'Renatio',
            'icon' => 'icon-search',
        ];
    }

    public function boot()
    {
        Entries::extend(function ($controller) {
            $controller->addJs('/plugins/renatio/seomanager/assets/js/main.js');
        });

        try {
            (new SeoCmsPage)->extend();
            (new SeoStaticPage)->extend();
            (new SeoBlog)->extend();
        } catch (\Exception $e) {
        }
    }

    public function register()
    {
        $this->registerConsoleCommand('seo:import-cms', SeoCmsPageImport::class);
        $this->registerConsoleCommand('seo:import-static', SeoStaticPageImport::class);
        $this->registerConsoleCommand('seo:import-blog', SeoBlogImport::class);
        $this->registerConsoleCommand('seo:migrate-tables', MigrateTables::class);
        $this->registerConsoleCommand('seo:patch', Patch::class);
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'renatio.seomanager::lang.settings.label',
                'description' => 'renatio.seomanager::lang.settings.description',
                'category' => 'renatio.seomanager::lang.settings.category',
                'icon' => 'octo-icon-code',
                'class' => Settings::class,
                'order' => 500,
                'keywords' => 'seo sem meta tags',
                'permissions' => ['renatio.seomanager.access_settings'],
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'renatio.seomanager.access_settings' => [
                'label' => 'renatio.seomanager::lang.permissions.settings',
                'tab' => 'renatio.seomanager::lang.permissions.tab',
            ],
            'renatio.seomanager.change_htaccess' => [
                'label' => 'renatio.seomanager::lang.permissions.change_htaccess',
                'tab' => 'renatio.seomanager::lang.permissions.tab',
            ],
        ];
    }

    public function registerComponents()
    {
        return [
            SeoTags::class => 'seoTags',
        ];
    }
}
