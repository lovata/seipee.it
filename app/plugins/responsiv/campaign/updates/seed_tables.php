<?php namespace Responsiv\Campaign\Updates;

use October\Rain\Database\Updates\Seeder;
use Responsiv\Campaign\Models\SubscriberList;

class SeedTables extends Seeder
{
    public function run()
    {
        SubscriberList::create([
            'name' => 'Followers',
            'code' => 'followers',
            'description' => 'People who are interested in hearing about news.'
        ]);
    }
}
