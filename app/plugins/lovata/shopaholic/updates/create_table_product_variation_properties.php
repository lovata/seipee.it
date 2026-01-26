<?php namespace Lovata\Shopaholic\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Class CreateTableProductVariationProperties
 * @package Lovata\Shopaholic\Updates
 */
class CreateTableProductVariationProperties extends Migration
{
    /**
     * Apply migration
     */
    public function up()
    {
        if (Schema::hasTable('lovata_shopaholic_product_variation_properties')) {
            return;
        }

        Schema::create('lovata_shopaholic_product_variation_properties', function (Blueprint $obTable) {
            $obTable->engine = 'InnoDB';
            $obTable->increments('id')->unsigned();
            $obTable->integer('variation_id')->unsigned();
            $obTable->integer('property_id')->unsigned();
            $obTable->integer('value_id')->unsigned();
            $obTable->timestamps();

            $obTable->index('variation_id', 'idx_variation_id');
            $obTable->index('property_id', 'idx_property_id');
            $obTable->index('value_id', 'idx_value_id');
        });
    }

    /**
     * Rollback migration
     */
    public function down()
    {
        Schema::dropIfExists('lovata_shopaholic_product_variation_properties');
    }
}
