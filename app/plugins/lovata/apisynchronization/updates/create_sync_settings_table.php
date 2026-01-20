<?php namespace Lovata\ApiSynchronization\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Create sync settings table
 */
class CreateSyncSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('lovata_apisync_settings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('sync_interval_hours')->default(4);
            $table->integer('sync_interval_minutes')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->string('cron_expression')->default('0 */4 * * *');
            $table->timestamps();
        });

        // Create default settings
        \DB::table('lovata_apisync_settings')->insert([
            'sync_interval_hours' => 4,
            'sync_interval_minutes' => 0,
            'is_enabled' => true,
            'cron_expression' => '0 */4 * * *',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('lovata_apisync_settings');
    }
}

