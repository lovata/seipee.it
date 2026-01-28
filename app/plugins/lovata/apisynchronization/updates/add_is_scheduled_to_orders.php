<?php namespace Lovata\ApiSynchronization\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

/**
 * Add is_scheduled field to lovata_orders_shopaholic_orders table
 */
class AddIsScheduledToOrders extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('lovata_orders_shopaholic_orders', 'is_scheduled')) {
            Schema::table('lovata_orders_shopaholic_orders', function($table) {
                $table->boolean('is_scheduled')->default(0)->after('is_delivered');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('lovata_orders_shopaholic_orders', 'is_scheduled')) {
            Schema::table('lovata_orders_shopaholic_orders', function($table) {
                $table->dropColumn('is_scheduled');
            });
        }
    }
}
