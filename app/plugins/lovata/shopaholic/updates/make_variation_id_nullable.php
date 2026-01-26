<?php namespace Lovata\Shopaholic\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Make variation_id nullable since we use variation_group now
 * Also remove old unique constraint that included variation_id
 */
class MakeVariationIdNullable extends Migration
{
    public function up()
    {
        Schema::table('lovata_shopaholic_product_variation_properties', function (Blueprint $table) {
            // Make variation_id nullable (deprecated field)
            $table->integer('variation_id')->unsigned()->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('lovata_shopaholic_product_variation_properties', function (Blueprint $table) {
            // Restore variation_id as NOT NULL
            $table->integer('variation_id')->unsigned()->nullable(false)->change();
        });
    }
}
