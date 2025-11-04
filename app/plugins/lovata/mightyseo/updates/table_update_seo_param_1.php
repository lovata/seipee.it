<?php namespace Lovata\MightySeo\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class TableUpdateSeoParam1 extends Migration
{
    const TABLE_NAME = 'lovata_mighty_seo_params';
    const FIELD_LIST = [
        'og_title',
        'og_type',
        'og_description',
        'og_image',
    ];

    /**
     * Apply migration
     */
    public function up()
    {
        if (!Schema::hasTable(self::TABLE_NAME) || Schema::hasColumns(self::TABLE_NAME, self::FIELD_LIST)) {
            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $obTable)
        {
            $obTable->string('og_type')->nullable();
            $obTable->text('og_title')->nullable();
            $obTable->text('og_description')->nullable();
            $obTable->text('og_image')->nullable();
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

        Schema::dropColumns(self::TABLE_NAME, self::FIELD_LIST);
    }
}
