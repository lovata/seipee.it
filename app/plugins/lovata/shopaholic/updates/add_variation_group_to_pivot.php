<?php namespace Lovata\Shopaholic\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * This allows grouping properties into variations without intermediate table
 */
class AddVariationGroupToPivot extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('lovata_shopaholic_product_variation_properties', 'variation_group')) {
            Schema::table('lovata_shopaholic_product_variation_properties', function (Blueprint $table) {
                $table->integer('product_id')->unsigned()->nullable()->after('id');

                $table->index('product_id', 'idx_pivot_product_id');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('lovata_shopaholic_product_variation_properties', 'variation_group')) {
            Schema::table('lovata_shopaholic_product_variation_properties', function (Blueprint $table) {
                $table->dropIndex('idx_pivot_product_id');
                $table->dropColumn(['product_id']);
            });
        }
    }
}
