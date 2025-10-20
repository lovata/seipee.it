<?php

namespace Renatio\SeoManager\Updates;

use October\Rain\Database\Updates\Migration;
use October\Rain\Support\Facades\File;

class CopyTailorBlueprints extends Migration
{
    public function up()
    {
        File::ensureDirectoryExists(app_path('blueprints/seo'));

        foreach (['meta_fields.yaml', 'og_fields.yaml'] as $file) {
            File::copy(plugins_path("renatio/seomanager/blueprints/$file"), app_path("blueprints/seo/$file"));
        }
    }

    public function down()
    {
        //
    }
}
