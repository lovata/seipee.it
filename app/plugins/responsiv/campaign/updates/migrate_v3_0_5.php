<?php

use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('responsiv_campaign_messages', 'processed_at')) {
            Schema::table('responsiv_campaign_messages', function($table) {
                $table->timestamp('processed_at')->nullable();
            });
        }
    }

    public function down()
    {
        // the field is important, so no going back
    }
};
