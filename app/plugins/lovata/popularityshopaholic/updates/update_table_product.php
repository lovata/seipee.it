<?php namespace Lovata\PopularityShopaholic\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Class UpdateTAbleProduct
 * @package Lovata\PopularityShopaholic\Updates
 */
class UpdateTAbleProduct extends Migration
{
    /**
     * Apply migration
     */
    public function up()
    {
        if (!Schema::hasTable('lovata_shopaholic_products') || Schema::hasColumn('lovata_shopaholic_products', 'popularity')) {
            return;
        }

        Schema::table('lovata_shopaholic_products', function (Blueprint $obTable) {
            $obTable->integer('popularity')->default(0);
        });
    }

    /**
     * Rollback migration
     */
    public function down()
    {
        if (!Schema::hasTable('lovata_shopaholic_products') || !Schema::hasColumn('lovata_shopaholic_products', 'popularity')) {
            return;
        }

        Schema::table('lovata_shopaholic_products', function (Blueprint $obTable) {
            $obTable->dropColumn(['popularity']);
        });
    }
}
