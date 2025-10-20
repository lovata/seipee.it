<?php

use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::table('responsiv_campaign_messages', function($table) {
            $table->boolean('is_dynamic_template')->default(false);
        });

        Schema::table('responsiv_campaign_messages_subscribers', function($table) {
            $table->text('content_html')->nullable();
        });
    }

    public function down()
    {
        Schema::table('responsiv_campaign_messages', function($table) {
            $table->dropColumn('is_dynamic_template');
        });

        Schema::table('responsiv_campaign_messages_subscribers', function($table) {
            $table->dropColumn('content_html');
        });
    }
};
