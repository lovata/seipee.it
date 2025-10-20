<?php namespace City\Map;

use City\Map\Console\SampleInstall;
use City\Map\Console\SampleRemove;
use October\Rain\Database\Relations\Relation;
use System\Classes\PluginBase;
use City\Map\Models\Marker;
use City\Map\Models\Source;

class Plugin extends PluginBase
{
    public function register()
    {
        $this->registerConsoleCommand('city.map.sample.install', SampleInstall::class);
        $this->registerConsoleCommand('city.map.sample.remove', SampleRemove::class);
    }

    public function boot()
    {
        Relation::morphMap([
            'marker' => Marker::class,
            'source' => Source::class,
        ]);
    }
}
