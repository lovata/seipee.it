<?php

use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('responsiv_campaign_lists', function($table)
        {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('code')->nullable()->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('responsiv_campaign_lists_subscribers', function($table)
        {
            $table->integer('list_id')->unsigned();
            $table->integer('subscriber_id')->unsigned();
            $table->primary(['list_id', 'subscriber_id'], 'list_subscriber');
        });
    }

    public function down()
    {
        Schema::dropIfExists('responsiv_campaign_lists');
        Schema::dropIfExists('responsiv_campaign_lists_subscribers');
    }
};
