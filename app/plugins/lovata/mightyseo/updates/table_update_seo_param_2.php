<?php namespace Lovata\MightySeo\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class TableUpdateSeoParam2 extends Migration
{
    const TABLE_NAME = 'lovata_mighty_seo_params';
    const FIELD_LIST = [
        'external_id',
    ];

    /**
     * Apply migration
     */
    public function up()
    {
        if (!Schema::hasTable(self::TABLE_NAME) || !Schema::hasColumns(self::TABLE_NAME, self::FIELD_LIST)) {
            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $obTable)
        {
            $obTable->string('external_id')->nullable()->change();
        });
    }

    /**
     * Rollback migration
     */
    public function down()
    {
        if (!Schema::hasTable(self::TABLE_NAME) || !Schema::hasColumns(self::TABLE_NAME, self::FIELD_LIST)) {
            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $obTable)
        {
            $obTable->integer('external_id')->nullable()->change();
        });
    }
}
