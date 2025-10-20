<?php namespace Tecnotrade\Blogmultisite\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddSiteFields extends Migration
{
    public function up()
    {
        Schema::table('rainlab_blog_posts', function ($table) {
            $table->integer('site_id')->unsigned()->index()->nullable();
        });
    }

    public function down()
    {
        Schema::table('rainlab_blog_posts', function ($table) {
            $table->dropColumn('site_id');
        });
    }
}
