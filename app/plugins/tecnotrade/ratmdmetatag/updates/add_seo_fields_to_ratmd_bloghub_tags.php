<?php namespace Tecnotrade\Ratmdmetatag\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddSeoFieldsToBlogHubTags extends Migration
{
    public function up()
    {
        Schema::table('ratmd_bloghub_tags', function ($table) {
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('canonical_url')->nullable();
            $table->string('meta_robots')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->text('og_image')->nullable();
            $table->text('og_url')->nullable();
            $table->string('og_type')->nullable()->default('website');
        });
    }

    public function down()
    {
        Schema::table('ratmd_bloghub_tags', function ($table) {
            $table->dropColumn([
                'meta_title', 'meta_description', 'meta_keywords', 'canonical_url',
                'meta_robots', 'og_title', 'og_description', 'og_image',
                'og_url', 'og_type'
            ]);
        });
    }
}