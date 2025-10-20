<?php

namespace OFFLINE\Boxes\Console;

use DB;
use Illuminate\Console\Command;
use October\Rain\Database\Scopes\MultisiteScope;
use OFFLINE\Boxes\Classes\PublishedState;
use OFFLINE\Boxes\Models\Page;
use Site;
use System\Models\SiteDefinition;

/**
 * Mirror a Boxes structure to another site.
 */
class MirrorBoxesSiteCommand extends Command
{
    /**
     * @var string name is the console command name
     */
    protected $signature = '
        boxes:mirror-site
        {--from= : The ID of the source site}
        {--to= : The ID of the target site}
    ';

    /**
     * @var string description is the console command description
     */
    protected $description = 'Mirrors a Boxes structure to another site.';

    /**
     * handle executes the console command
     */
    public function handle()
    {
        $sourceSite = $this->option('from');
        $targetSite = $this->option('to');

        if (!$sourceSite || !$targetSite) {
            $this->error('Please provide a --from= and --to= parameter specifying source and target site IDs.');

            return;
        }

        if (!$this->confirm('!!! WARNING !!! All data on the target site will be deleted. Are you sure you want to continue?')) {
            return;
        }

        $sourceSiteModel = SiteDefinition::findOrFail($sourceSite);
        $targetSiteModel = SiteDefinition::findOrFail($targetSite);

        $this->info("Mirroring pages from Site {$sourceSiteModel->name} to {$targetSiteModel->name}:");

        DB::transaction(function () use ($sourceSite, $targetSite, $targetSiteModel) {
            $this->info('Deleting existing site data...');

            Page::withoutGlobalScope(MultisiteScope::class)->where('site_id', $targetSite)->get()->each->delete();

            Page::withoutGlobalScope(MultisiteScope::class)
                ->whereNull('parent_id')
                ->where('site_id', $sourceSite)
                ->where('published_state', PublishedState::DRAFT)
                ->get()
                ->each(function (Page $page) use ($targetSiteModel) {
                    Site::withGlobalContext(function () use ($targetSiteModel, $page) {
                        $replicate = function (Page $page, $parentId = null, $level = 0) use (
                            $targetSiteModel,
                            &$replicate
                        ) {
                            $newPage = $page->replicateWithRelations(['url', 'slug', 'site_id', 'translations']);
                            $newPage->slug = $page->slug;
                            $newPage->url = $page->url;
                            $newPage->parent_id = $parentId;
                            $newPage->site_id = $targetSiteModel->id;
                            $newPage->save();

                            $newPage->site_root_id = $page->site_root_id;
                            $newPage->save();

                            $this->comment(str_repeat(' ', $level * 2) . "- Mirrored '{$page->name}'");

                            $children = $page->getChildren();
                            $children?->each(function (Page $child) use ($replicate, $newPage, $level) {
                                $replicate($child, $newPage->id, ++$level);
                            });
                        };

                        $replicate($page);
                    });
                });
        });
    }
}
