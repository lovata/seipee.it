<?php namespace Lovata\BaseCode\updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * Class TableUpdateBuddiesUsersAddParentPermissionFields
 * @package Lovata\PropertiesShopaholic\Updates
 */
class TableUpdateBuddiesUsersAddParentPermissionFields extends Migration
{
    const TABLE_NAME = 'lovata_buddies_users';

    /**
     * Apply migration
     */
    public function up()
    {
        if(!Schema::hasTable(self::TABLE_NAME)) {
            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $obTable)
        {
            $obTable->boolean('b2b_permission')->default(0);
            $obTable->integer('parent_id')->unsigned()->nullable();

            $obTable->foreign('parent_id')->references('id')->on(self::TABLE_NAME)->onDelete('cascade');
        });
    }

    /**
     * Rollback migration
     */
    public function down()
    {
        if(!Schema::hasTable(self::TABLE_NAME) || !Schema::hasColumn(self::TABLE_NAME, 'b2b_permission') || !Schema::hasColumn(self::TABLE_NAME, 'parent_id')) {
            return;
        }

        Schema::table(self::TABLE_NAME, function(Blueprint $obTable)
        {
            $obTable->dropColumn(['parent_id']);
            $obTable->dropColumn(['b2b_permission']);
        });
    }
}
