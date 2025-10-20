<?php namespace Tecnotrade\Tecnotradegallery\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateTecnotradeTecnotradegalleryGalleries extends Migration
{
    public function up()
    {
        Schema::create('tecnotrade_tecnotradegallery_galleries', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('slug');
            $table->text('images')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('tecnotrade_tecnotradegallery_galleries');
    }
}
