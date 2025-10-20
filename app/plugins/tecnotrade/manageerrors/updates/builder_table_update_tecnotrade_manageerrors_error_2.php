<?php namespace Tecnotrade\Manageerrors\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateTecnotradeManageerrorsError2 extends Migration
{
    public function up()
    {
        Schema::table('tecnotrade_manageerrors_error', function($table)
        {
            $table->integer('import_status')->unsigned()->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('tecnotrade_manageerrors_error', function($table)
        {
            $table->dropColumn('import_status');
        });
    }
}
