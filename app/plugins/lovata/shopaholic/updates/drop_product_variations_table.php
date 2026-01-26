<?php namespace Lovata\Shopaholic\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

/**
 * Drop deprecated ProductVariation table
 * We use pivot table directly with variation_group field
 */
class DropProductVariationsTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('lovata_shopaholic_product_variations');
    }

    public function down()
    {
        // No rollback - table was deprecated
    }
}
