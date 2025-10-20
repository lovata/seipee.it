<?php

namespace Renatio\SeoManager\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Patch extends Command
{
    protected $signature = 'seo:patch {patch}';

    protected $description = 'Apply specified patch.';

    public function handle()
    {
        $patch = str_replace('.', 'Point', $this->argument('patch'));

        $this->{'patch'.$patch}();
    }

    /*
     * Migrate data from renatio_seomanager_seo_tags table to models that implement SeoModel behavior.
     */
    protected function patch3Point0()
    {
        if (! $this->confirm('This will patch your database for SeoManager plugin 3.0. Please make sure you have a backup before proceeding. Proceed?')) {
            return;
        }

        DB::table('renatio_seomanager_seo_tags')
            ->whereNotNull('seo_tag_type')
            ->whereNotNull('seo_tag_id')
            ->orderBy('id')
            ->chunk(100, function ($tags) {
                foreach ($tags as $tag) {
                    $table = (new $tag->seo_tag_type)->getTable();

                    DB::table($table)
                        ->where('id', $tag->seo_tag_id)
                        ->update([
                            'meta_title' => $tag->meta_title,
                            'meta_description' => $tag->meta_description,
                            'meta_keywords' => $tag->meta_keywords,
                            'robot_index' => $tag->robot_index,
                            'robot_follow' => $tag->robot_follow,
                            'robot_advanced' => $tag->robot_advanced,
                            'canonical_url' => $tag->canonical_url,
                            'redirect_url' => $tag->redirect_url,
                            'og_title' => $tag->og_title,
                            'og_type' => $tag->og_type,
                            'og_description' => $tag->og_description,
                            'og_image' => $tag->og_image,
                        ]);
                }
            });

        $this->info('SeoManager plugin patch for version 3.0 applied!');
    }
}
