<?php namespace Lovata\ApiSynchronization\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateProductAliasesTable extends Migration
{
    public function up()
    {
        Schema::create('lovata_user_product_aliases', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('product_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();

            $table->string('alias');

            $table->timestamps();

            $table->unique(['product_id', 'user_id'], 'unique_product_user_alias');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lovata_user_product_aliases');
    }
}
