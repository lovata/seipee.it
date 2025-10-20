<?php

namespace Renatio\SeoManager\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use October\Rain\Database\Model;
use Renatio\SeoManager\Behaviors\SeoModel;
use Schema;
use System\Classes\PluginManager;
use Throwable;

class MigrateTables extends Command
{
    protected $signature = 'seo:migrate-tables {--table=}';

    protected $description = 'Migrate tables for models implementing SEO behavior to add SEO columns.';

    public function handle()
    {
        if ($this->option('table')) {
            $this->migrateSingleTable();
        } else {
            $this->migrateAllTables();
        }
    }

    protected function migrateSingleTable($table = null)
    {
        $table = $table ?? $this->option('table');

        if (! Schema::hasTable($table)) {
            return $this->error(sprintf('Table "%s" does not exist.', $table));
        }

        if (Schema::hasColumn($table, 'meta_title')) {
            return $this->warn(sprintf('Table "%s" already has SEO columns migrated.', $table));
        }

        $this->runMigration($table);

        $this->info(sprintf('Migration for table "%s" run successfully.', $table));
    }

    protected function migrateAllTables()
    {
        if (! ($tables = $this->getTables())) {
            return $this->warn('No tables found to migrate. Did you implemented "SeoModel" behavior in your models?');
        }

        $this->line('Following tables will be migrated:');
        $this->line('');

        foreach ($tables as $table) {
            $this->line($table);
        }

        if ($this->confirm('Do you wish to continue?')) {
            foreach ($tables as $table) {
                $this->migrateSingleTable($table);
            }
        }
    }

    protected function runMigration($table)
    {
        Schema::table($table, function (Blueprint $table) {
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('redirect_url')->nullable();
            $table->string('robot_index')->default('index')->nullable();
            $table->string('robot_follow')->default('follow')->nullable();
            $table->string('robot_advanced')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_type')->nullable();
            $table->string('og_image')->nullable();
        });
    }

    protected function getTables()
    {
        $tables = [];

        foreach (PluginManager::instance()->getVendorAndPluginNames() as $plugins) {
            foreach ($plugins as $plugin) {
                $modelsPath = plugins_path($plugin.'/models');

                if (! File::exists($modelsPath)) {
                    continue;
                }

                $files = File::glob($modelsPath.'/*.php');

                foreach ($files as $file) {
                    $class = str_replace([plugins_path(), '.php'], '', $file);
                    $class = str_replace('/', '\\', $class);

                    try {
                        $model = new $class;

                        if ($model instanceof Model && $model->isClassExtendedWith(SeoModel::class)) {
                            $tables[] = $model->getTable();
                        }
                    } catch (Throwable $exception) {
                        //
                    }
                }
            }
        }

        return $tables;
    }
}
