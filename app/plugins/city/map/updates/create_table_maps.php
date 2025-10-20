<?php namespace City\Map\Updates;

use October\Rain\Database\Schema\Blueprint;
use Schema;
use October\Rain\Database\Updates\Migration;

class CreateTableMaps extends Migration
{
    public function up()
    {
        Schema::create('city_map_maps', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name', 200);
            $table->string('lat', 50);
            $table->string('lng', 50);
            $table->unsignedTinyInteger('zoom');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('city_map_relations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->unsignedInteger('map_id');
            $table->unsignedInteger('relation_id');
            $table->string('relation_type', 50);
            $table->primary(
                ['map_id', 'relation_id', 'relation_type'],
                'map_relation'
            );
        });
    }

    public function down()
    {
        Schema::dropIfExists('city_map_maps');
        Schema::dropIfExists('city_map_relations');
    }
}
