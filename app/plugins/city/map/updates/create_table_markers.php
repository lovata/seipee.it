<?php namespace City\Map\Updates;

use October\Rain\Database\Schema\Blueprint;
use Schema;
use October\Rain\Database\Updates\Migration;

class CreateTableMarkers extends Migration
{
    public function up()
    {
        Schema::create('city_map_markers', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 200);
            $table->string('type', 200);
            $table->string('lat', 50);
            $table->string('lng', 50);
            $table->string('color', 20)->nullable();
            $table->string('image', 250)->nullable();
            $table->string('size', 50)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('city_map_markers');
    }
}
