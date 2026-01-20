<?php namespace Lovata\ApiSynchronization\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Add additional fields to orders table for Seipee sync
 */
class AddSeipeeFieldsToOrders extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('lovata_orders_shopaholic_orders')) {
            return;
        }

        Schema::table('lovata_orders_shopaholic_orders', function (Blueprint $table) {
            // Payment type from CD_PG field
            if (!Schema::hasColumn('lovata_orders_shopaholic_orders', 'payment_type')) {
                $table->string('payment_type', 50)->nullable()->after('payment_method_id');
            }

            // Delivery date from DataConsegna
            if (!Schema::hasColumn('lovata_orders_shopaholic_orders', 'delivery_date')) {
                $table->dateTime('delivery_date')->nullable()->after('created_at');
            }

            // Is delivered flag from DocEvaso
            if (!Schema::hasColumn('lovata_orders_shopaholic_orders', 'is_delivered')) {
                $table->boolean('is_delivered')->default(false)->after('delivery_date');
            }

            // Total items count (calculated from positions)
            if (!Schema::hasColumn('lovata_orders_shopaholic_orders', 'items_count')) {
                $table->integer('items_count')->default(0)->after('is_delivered');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('lovata_orders_shopaholic_orders')) {
            return;
        }

        Schema::table('lovata_orders_shopaholic_orders', function (Blueprint $table) {
            if (Schema::hasColumn('lovata_orders_shopaholic_orders', 'payment_type')) {
                $table->dropColumn('payment_type');
            }
            if (Schema::hasColumn('lovata_orders_shopaholic_orders', 'delivery_date')) {
                $table->dropColumn('delivery_date');
            }
            if (Schema::hasColumn('lovata_orders_shopaholic_orders', 'is_delivered')) {
                $table->dropColumn('is_delivered');
            }
            if (Schema::hasColumn('lovata_orders_shopaholic_orders', 'items_count')) {
                $table->dropColumn('items_count');
            }
        });
    }
}

