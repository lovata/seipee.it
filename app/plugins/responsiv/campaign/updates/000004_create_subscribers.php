<?php

use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('responsiv_campaign_subscribers', function($table) {
            $table->increments('id');
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('email', 100)->nullable()->index();
            $table->string('created_ip_address')->nullable();
            $table->string('confirmed_ip_address')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->string('message_type')->nullable()->default('html');
            $table->mediumText('meta_data')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('responsiv_campaign_subscribers');
    }
};
