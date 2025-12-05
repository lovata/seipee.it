<?php namespace Lovata\ApiSynchronization\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddExternalIdToOrderPositions extends Migration
{
    const TABLE_NAME = 'lovata_orders_shopaholic_order_positions';
    const FIELD_LIST = ['external_id'];

    public function up()
    {
        if (!Schema::hasTable(self::TABLE_NAME) || Schema::hasColumns(self::TABLE_NAME, self::FIELD_LIST)) {
            return;
        }

        Schema::table(self::TABLE_NAME, function($table)
        {
            $table->string('external_id')->nullable()->after('id');
            $table->index('external_id');
        });
    }

    public function down()
    {
        if (!Schema::hasTable(self::TABLE_NAME) || !Schema::hasColumns(self::TABLE_NAME, self::FIELD_LIST)) {
            return;
        }

        Schema::dropColumns(self::TABLE_NAME, self::FIELD_LIST);
    }
}
