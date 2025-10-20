<?php

namespace OFFLINE\Boxes\Console;

use Illuminate\Console\Command;
use OFFLINE\Boxes\Classes\CMS\ThemeResolver;
use OFFLINE\Boxes\Classes\Transfer\ImportExport;
use Symfony\Component\Console\Output\OutputInterface;
use System\Helpers\Cache;

/**
 * Import Boxes from the filesystem.
 */
class ImportBoxesCommand extends Command
{
    /**
     * @var string name is the console command name
     */
    protected $signature = '
        boxes:import
        {--source-dir= : The directory where the boxes should be imported from, defaults to theme/<active-theme>/boxes}
        {--no-attachments : Do not import attachments from the filesystem}
        {--tag= : Import a tagged export with that tag }
    ';

    /**
     * @var string description is the console command description
     */
    protected $description = 'Import all Boxes data from the filesystem';

    /**
     * handle executes the console command
     */
    public function handle()
    {
        $this->error('!!!');
        $this->error('!!! This command is no longer supported and will be removed in the next version.');
        $this->error('!!!');

        if ($this->confirm('Do you want to proceed?') === false) {
            return;
        }

        $theme = ThemeResolver::instance()?->getThemeCode();

        $sourceDir = $this->option('source-dir') ?: themes_path($theme . '/boxes');

        if ($tag = $this->option('tag')) {
            if (in_array($tag, ['pages', 'attachments'])) {
                $this->error('The tag names "pages" and "attachments" are reserved and cannot be used.');

                return;
            }

            $sourceDir .= '/' . $tag;
        }

        $this->info("Importing pages from {$sourceDir}...", OutputInterface::VERBOSITY_VERBOSE);

        $transfer = new ImportExport($sourceDir);

        $pages = $transfer->import(!$this->option('no-attachments'));

        $this->getOutput()->success(sprintf('Imported %d pages', $pages->count()));

        // Make sure no cached Boxes data is used.
        Cache::clear();
    }
}
