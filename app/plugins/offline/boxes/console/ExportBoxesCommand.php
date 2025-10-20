<?php

namespace OFFLINE\Boxes\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use October\Rain\Support\Facades\Str;
use OFFLINE\Boxes\Classes\CMS\ThemeResolver;
use OFFLINE\Boxes\Classes\Transfer\ImportExport;
use OFFLINE\Boxes\Models\Box;
use OFFLINE\Boxes\Models\Page;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Export Boxes to the filesystem.
 */
class ExportBoxesCommand extends Command
{
    /**
     * @var string name is the console command name
     */
    protected $signature = '
        boxes:export
        {--target-dir= : The directory where the boxes should be exported to, defaults to theme/<active-theme>/boxes}
        {--no-attachments : Do not export attachments to the filesystem}
        {--partial : Run a partial export }
        {--tag= : A tagged export gets its own folder  }
        {--pages= : Export pages for partial exports, comma-separated list of IDs }
    ';

    /**
     * @var string description is the console command description
     */
    protected $description = 'Export all Boxes data to the filesystem';

    /**
     * handle executes the console command
     */
    public function handle()
    {
        $this->error('!!!');
        $this->error('!!! This command is no longer supported and will be removed in the next version.');
        $this->error('!!!');

        $pages = $this->baseQuery()->get();
        $theme = ThemeResolver::instance()?->getThemeCode();

        $targetDir = $this->option('target-dir') ?: themes_path($theme . '/boxes');

        if ($tag = $this->option('tag')) {
            if (in_array($tag, ['pages', 'attachments'])) {
                $this->error('The tag names "pages" and "attachments" are reserved and cannot be used.');

                return;
            }

            $targetDir .= '/' . $tag;
        }

        $this->info("Exporting pages to {$targetDir}...", OutputInterface::VERBOSITY_VERBOSE);

        $type = $this->option('partial') ? 'partial' : 'full';

        $isPartial = false;

        if (Str::contains($type, 'partial', true)) {
            $isPartial = true;
            $pages = $this->handlePartialExport();
        }

        $transfer = new ImportExport($targetDir);
        $transfer->export($pages->toNested(false), !$this->option('no-attachments'), $isPartial);

        $this->getOutput()->success(sprintf('Exported %d pages to %s', $pages->count(), $targetDir));
    }

    protected function baseQuery(): Builder
    {
        $relations = (new Box())->extractRelations();

        return Page::currentPublished()->with(['images', 'boxes' => $relations]);
    }

    private function handlePartialExport()
    {
        $this->info("You can export the following top-level pages:\n");
        $this->table(['ID', 'Slug', 'Name'], Page::currentPublished()->whereNull('parent_id')->get()->map(fn (Page $page) => [
            $page->id,
            $page->slug,
            $page->name,
        ]));

        $choices = $this->option('pages');

        if ($choices === null) {
            $choices = $this->ask(
                'Which pages do you want to export? Enter a list of comma-separated IDs',
            );
        }

        if (!$choices) {
            $this->warn('No pages selected, aborting export.');

            return;
        }

        $pageIds = array_map('intval', explode(',', $choices));

        if (count($pageIds) === 0) {
            $this->warn('No pages selected, aborting export.');

            return;
        }

        return $this->baseQuery()->whereIn('id', $pageIds)->get();
    }
}
