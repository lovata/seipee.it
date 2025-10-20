<?php namespace Tecnotrade\Manageerrors\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteTecnotradeManageerrorsErrors extends Migration
{
    public function up()
    {
        Schema::dropIfExists('tecnotrade_manageerrors_errors');
    }
    
    public function down()
    {
        Schema::create('tecnotrade_manageerrors_errors', function($table)
        {
            $table->increments('id')->unsigned();
            $table->text('error_message')->nullable();
            $table->string('error_code', 255)->nullable();
            $table->integer('import_status')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
}
