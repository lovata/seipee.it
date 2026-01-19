<?php namespace Lovata\BaseCode\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Class TableCreateRequestQuotations
 * @package Lovata\Basecode\Updates
 */
class TableCreateRequestQuotations extends Migration
{
    const TABLE_NAME = 'lovata_basecode_request_quotations';

    /**
     * Apply migration
     */
    public function up()
    {
        if (Schema::hasTable(self::TABLE_NAME)) {
            return;
        }

        Schema::create(self::TABLE_NAME, function(Blueprint $obTable)
        {
            $obTable->increments('id');
            $obTable->string('title', 255);
            $obTable->string('notes', 255);
            $obTable->text('variants')->nullable();
            $obTable->unsignedInteger('product_id');
            $obTable->unsignedInteger('user_id');
            $obTable->timestamps();
        });
    }

    /**
     * Rollback migration
     */
    public function down()
    {
        Schema::dropIfExists(self::TABLE_NAME);
    }
}