<?php namespace Lovata\ApiSynchronization\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddCustomerFieldsToUsers extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('lovata_buddies_users')) {
            return;
        }

        Schema::table('lovata_buddies_users', function ($table) {
            if (!Schema::hasColumn('lovata_buddies_users', 'erp_user_code')) {
                $table->string('erp_user_code')->nullable()->index();
            }
            if (!Schema::hasColumn('lovata_buddies_users', 'external_id')) {
                $table->string('external_id')->nullable()->index();
            }
            if (!Schema::hasColumn('lovata_buddies_users', 'alternate_destination_code')) {
                $table->string('alternate_destination_code')->nullable();
            }
            if (!Schema::hasColumn('lovata_buddies_users', 'payment')) {
                $table->string('payment')->nullable();
            }
            if (!Schema::hasColumn('lovata_buddies_users', 'shipping')) {
                $table->string('shipping')->nullable();
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('lovata_buddies_users')) {
            return;
        }

        Schema::table('lovata_buddies_users', function ($table) {
            foreach ([
                'erp_user_code', 'external_id', 'alternate_destination_code', 'payment', 'shipping'
            ] as $column) {
                if (Schema::hasColumn('lovata_buddies_users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
