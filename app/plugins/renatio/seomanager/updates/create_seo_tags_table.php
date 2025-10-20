<?php

namespace Renatio\SeoManager\Updates;

use Illuminate\Support\Facades\Artisan;
use October\Rain\Database\Updates\Migration;

class CreateSeoTagsTable extends Migration
{
    public function up()
    {
        Artisan::call('seo:import-cms');
        Artisan::call('seo:import-static');
        Artisan::call('seo:import-blog');
    }

    public function down()
    {
        //
    }
}
