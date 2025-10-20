<?php namespace City\Map\Console;

use City\Map\Models\Map;
use City\Map\Models\Marker;
use City\Map\Models\Source;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * SampleRemove Command
 */
class SampleRemove extends Command
{
    /**
     * @var string name is the console command name
     */
    protected $name = 'city:map:sample:remove';

    /**
     * @var string description is the console command description
     */
    protected $description = 'Remove sample data';

    /**
     * handle executes the console command
     */
    public function handle()
    {
        if (! $this->confirm('Remove sample data?')) {
            return;
        }

        Map::where('name', 'like', '%(Sample)')
            ->delete();

        Marker::where('name', 'like', '%(Sample)')
            ->delete();

        Source::where('name', 'like', '%(Sample)')
            ->delete();

        $this->warn('Sample data have been removed');
    }

    /**
     * getArguments get the console command arguments
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * getOptions get the console command options
     */
    protected function getOptions()
    {
        return [];
    }
}
