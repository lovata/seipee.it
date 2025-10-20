<?php namespace Tecnotrade\Manageerrors\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateTecnotradeManageerrorsError extends Migration
{
    public function up()
    {
        Schema::create('tecnotrade_manageerrors_error', function($table)
        {
            $table->increments('id')->unsigned();
            $table->integer('error_code')->unsigned();
            $table->text('error_message');
            $table->dateTime('error_date')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('tecnotrade_manageerrors_error');
    }
}
