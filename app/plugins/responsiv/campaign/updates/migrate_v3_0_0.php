<?php

use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::table('responsiv_campaign_messages', function($table) {
            $table->integer('site_id')->nullable()->index();
            $table->integer('site_root_id')->nullable()->index();
        });

        // Set multisite defaults
        Db::table('responsiv_campaign_messages')->update([
            'site_id' => 1,
            'site_root_id' => Db::raw('id')
        ]);
    }

    public function down()
    {
        Schema::table('responsiv_campaign_messages', function($table) {
            $table->dropColumn('site_id');
            $table->dropColumn('site_root_id');
        });
    }
};
