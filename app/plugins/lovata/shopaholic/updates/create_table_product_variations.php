<?php namespace Lovata\Shopaholic\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Class CreateTableProductVariations
 * @package Lovata\Shopaholic\Updates
 */
class CreateTableProductVariations extends Migration
{
    /**
     * Apply migration
     */
    public function up()
    {
        if (Schema::hasTable('lovata_shopaholic_product_variations')) {
            return;
        }

        Schema::create('lovata_shopaholic_product_variations', function (Blueprint $obTable) {
            $obTable->engine = 'InnoDB';
            $obTable->increments('id')->unsigned();
            $obTable->integer('product_id')->unsigned();
            $obTable->string('external_id')->nullable();
            $obTable->string('name')->nullable();
            $obTable->timestamps();

            $obTable->index('product_id', 'idx_product_id');
            $obTable->index('external_id', 'idx_external_id');
        });
    }

    /**
     * Rollback migration
     */
    public function down()
    {
        Schema::dropIfExists('lovata_shopaholic_product_variations');
    }
}
