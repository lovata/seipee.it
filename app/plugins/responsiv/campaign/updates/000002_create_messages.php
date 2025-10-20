<?php

use October\Rain\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('responsiv_campaign_messages', function($table) {
            $table->increments('id');
            $table->integer('status_id')->unsigned()->index()->nullable();
            $table->string('page')->nullable()->index();
            $table->string('name')->nullable();
            $table->string('subject')->nullable();
            $table->mediumText('content')->nullable();
            $table->mediumText('content_html')->nullable();
            $table->mediumText('syntax_data')->nullable();
            $table->mediumText('syntax_fields')->nullable();
            $table->string('stagger_type')->nullable();
            $table->integer('stagger_count')->nullable();
            $table->integer('count_subscriber')->nullable()->default(0);
            $table->integer('count_sent')->nullable()->default(0);
            $table->integer('count_read')->nullable()->default(0);
            $table->integer('count_stop')->nullable()->default(0);
            $table->integer('count_repeat')->nullable()->default(1);
            $table->boolean('is_delayed')->default(false);
            $table->timestamp('launch_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->boolean('is_staggered')->default(false);
            $table->integer('stagger_time')->nullable();
            $table->boolean('is_repeating')->default(false);
            $table->string('repeat_frequency')->nullable();
            $table->text('groups')->nullable();
            $table->timestamps();
        });

        Schema::create('responsiv_campaign_messages_lists', function($table) {
            $table->integer('message_id')->unsigned();
            $table->integer('list_id')->unsigned();
            $table->primary(['message_id', 'list_id'], 'message_list');
        });

        Schema::create('responsiv_campaign_messages_subscribers', function($table) {
            $table->integer('message_id')->unsigned();
            $table->integer('subscriber_id')->unsigned();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('stop_at')->nullable();
            $table->primary(['message_id', 'subscriber_id'], 'message_subscriber');
        });
    }

    public function down()
    {
        Schema::dropIfExists('responsiv_campaign_messages');
        Schema::dropIfExists('responsiv_campaign_messages_lists');
        Schema::dropIfExists('responsiv_campaign_messages_subscribers');
    }
};
