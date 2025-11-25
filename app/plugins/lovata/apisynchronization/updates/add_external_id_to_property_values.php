<?php namespace Lovata\ApiSynchronization\updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class AddExternalIdToPropertyValues extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('lovata_properties_shopaholic_values', 'external_id')) {
            Schema::table('lovata_properties_shopaholic_values', function ($table) {
                $table->string('external_id')->nullable()->unique();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('lovata_properties_shopaholic_values', 'external_id')) {
            Schema::table('lovata_properties_shopaholic_values', function ($table) {
                // Drop unique index if it exists; Laravel will infer name or we can specify
                try {
                    $table->dropUnique(['external_id']);
                } catch (\Throwable $e) {
                    // ignore
                }
                $table->dropColumn('external_id');
            });
        }
    }
}
