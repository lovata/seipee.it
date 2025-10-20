<?php namespace Inetis\DownloadManager\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateCategoriesUserGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('inetis_downloadmanager_categories_user_groups', function ($table) {
            $table->engine = 'InnoDB';
            $table->integer('category_id')->unsigned();
            $table->integer('user_group_id')->unsigned();
            $table->primary(['category_id', 'user_group_id'], 'category_user_group');
        });

    }

    public function down()
    {
        Schema::dropIfExists('inetis_downloadmanager_categories_user_groups');
    }
}
