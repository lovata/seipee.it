<?php namespace City\Map\Console;

use City\Map\Models\Map;
use City\Map\Models\Marker;
use City\Map\Models\Source;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * SampleInstall Command
 */
class SampleInstall extends Command
{
    /**
     * @var string name is the console command name
     */
    protected $name = 'city:map:sample:install';

    /**
     * @var string description is the console command description
     */
    protected $description = 'Install sample data';

    /**
     * handle executes the console command
     */
    public function handle()
    {
        /**
         * Create maps
         */
        $mapsData = [
            'australia' => [
                'name' => 'Australia (Sample)',
                'lat' => '-26.164446466202',
                'lng' => '134.19852462551',
                'zoom' => '5',
                'is_active' => true,
            ],

            'madagascar' => [
                'name' => 'Madagascar (Sample)',
                'lat' => '-17.979676859258202',
                'lng' => '47.43854683427121',
                'zoom' => '6',
                'is_active' => true,
            ],
        ];

        $maps = [];
        Map::unguard();
        foreach ($mapsData as $name => $data) {
            // $map = Map::firstOrCreate(['name' => $data['name']], $data);
            $map = Map::create($data);
            $maps[$name] = $map->id;
        }

        $this->info('Maps created');

        /**
         * Create markers
         */
        $markers = [
            [
                'name' => 'Lemur (Sample)',
                'type' => 'marker',
                'lat' => '-20.843992117496175',
                'lng' => '47.47818289686043',
                'description' => '<p><strong>Lemur</strong>. Ranging in size from the 30 g Madame Berthe\'s mouse lemur,
                    the world\'s smallest primate, to the recently extinct 160–200 kg Archaeoindris fontoynonti, lemurs
                    evolved diverse forms of locomotion, varying levels of social complexity, and unique adaptations to
                    the local climate. Source: <a href="https://en.wikipedia.org/wiki/Lemur" rel="noopener noreferrer"
                    target="_blank">https://en.wikipedia.org/wiki/Lemur</a></p>',
                'is_active' => true,
                'maps' => $maps['madagascar'],
            ],

            [
                'name' => 'Chameleon (Sample)',
                'type' => 'marker',
                'lat' => '-18.068125266033146',
                'lng' => '45.73135676128709',
                'color' => '#8e44ad',
                'description' => '<p><strong>Chameleon</strong>. Different chameleon species are able to vary their coloration
                    and pattern through combinations of pink, blue, red, orange, green, black, brown, light blue, yellow,
                    turquoise, and purple. Source: <a href="https://en.wikipedia.org/wiki/Chameleon" rel="noopener noreferrer"
                    target="_blank">https://en.wikipedia.org/wiki/Chameleon</a></p>',
                'is_active' => true,
                'maps' => $maps['madagascar'],
            ],

            [
                'name' => 'Tomato frog (Sample)',
                'type' => 'circle',
                'lat' => '-15.533682012956898',
                'lng' => '49.49966728711762',
                'color' => '#c0392b',
                'size' => '50000',
                'is_active' => true,
                'maps' => $maps['madagascar'],
            ],
        ];

        Marker::unguard();
        foreach ($markers as $data) {
            Marker::create($data);
        }

        $this->info('Markers created');

        /**
         * Create sources
         */
        $sources = [
            [
                'name' => 'Random Places (Sample)',
                'type' => 'custom',
                'value' => 'City\Map\Classes\Source\Sample\Places',
                'is_active' => true,
                'maps' => $maps['australia'],
            ]
        ];

        Source::unguard();
        foreach ($sources as $data) {
            Source::create($data);
        }

        $this->info('Sources created');
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
