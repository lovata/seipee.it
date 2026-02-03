<?php namespace Lovata\ApiSynchronization\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Add warehouse_internal and warehouse_external fields to offers table
 */
class AddWarehouseFieldsToOffers extends Migration
{
    public function up()
    {
        Schema::table('lovata_shopaholic_offers', function (Blueprint $table) {
            $table->integer('warehouse_internal')->default(0)->after('quantity');
            $table->integer('warehouse_external')->default(0)->after('warehouse_internal');

            $table->index('warehouse_internal', 'idx_warehouse_internal');
            $table->index('warehouse_external', 'idx_warehouse_external');
        });
    }

    public function down()
    {
        Schema::table('lovata_shopaholic_offers', function (Blueprint $table) {
            $table->dropIndex('idx_warehouse_internal');
            $table->dropIndex('idx_warehouse_external');
            $table->dropColumn(['warehouse_internal', 'warehouse_external']);
        });
    }
}
