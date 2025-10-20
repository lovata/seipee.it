<?php namespace Tecnotrade\Manageerrors\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTecnotradeManageerrorsError extends Migration
{
    public function up()
    {
        Schema::table('tecnotrade_manageerrors_error', function($table)
        {
            $table->string('page_url')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('tecnotrade_manageerrors_error', function($table)
        {
            $table->dropColumn('page_url');
        });
    }
}
