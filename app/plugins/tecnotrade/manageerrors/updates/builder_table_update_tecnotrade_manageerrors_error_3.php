<?php namespace Tecnotrade\Manageerrors\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTecnotradeManageerrorsError3 extends Migration
{
    public function up()
    {
        Schema::table('tecnotrade_manageerrors_error', function($table)
        {
            $table->integer('error_line')->unsigned();
            $table->string('error_file')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('tecnotrade_manageerrors_error', function($table)
        {
            $table->dropColumn('error_line');
            $table->dropColumn('error_file');
        });
    }
}
