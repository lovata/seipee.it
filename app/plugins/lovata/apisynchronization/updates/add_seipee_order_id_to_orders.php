<?php namespace Lovata\ApiSynchronization\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddSeipeeOrderIdToOrders extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('lovata_orders_shopaholic_orders', 'seipee_order_id')) {
            Schema::table('lovata_orders_shopaholic_orders', function($table)
            {
                $table->integer('seipee_order_id')->nullable()->after('id');
                $table->index('seipee_order_id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('lovata_orders_shopaholic_orders', 'seipee_order_id')) {
            Schema::table('lovata_orders_shopaholic_orders', function($table)
            {
                $table->dropIndex(['seipee_order_id']);
                $table->dropColumn('seipee_order_id');
            });
        }
    }
}
