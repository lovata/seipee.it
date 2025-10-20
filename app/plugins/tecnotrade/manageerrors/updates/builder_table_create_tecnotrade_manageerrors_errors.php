<?php namespace Tecnotrade\Manageerrors\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateTecnotradeManageerrorsErrors extends Migration
{
    public function up()
    {
        Schema::create('tecnotrade_manageerrors_errors', function($table)
        {
            $table->increments('id');
            $table->text('error_message')->nullable();
            $table->string('error_code')->nullable();
            $table->integer('import_status')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('tecnotrade_manageerrors_errors');
    }
}
