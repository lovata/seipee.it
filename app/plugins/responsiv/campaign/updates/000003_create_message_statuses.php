<?php

use October\Rain\Database\Updates\Migration;
use Responsiv\Campaign\Models\MessageStatus;

return new class extends Migration
{
    public function up()
    {
        Schema::create('responsiv_campaign_message_statuses', function($table)
        {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('code')->nullable()->index();
            $table->timestamps();
        });

        MessageStatus::create(['name' => 'Draft', 'code' => 'draft']);
        MessageStatus::create(['name' => 'Sent', 'code' => 'sent']);
        MessageStatus::create(['name' => 'Pending', 'code' => 'pending']);
        MessageStatus::create(['name' => 'Active', 'code' => 'active']);
        MessageStatus::create(['name' => 'Cancelled', 'code' => 'cancelled']);
        MessageStatus::create(['name' => 'Archived', 'code' => 'archived']);
        MessageStatus::create(['name' => 'Processing', 'code' => 'processing']);
    }

    public function down()
    {
        Schema::dropIfExists('responsiv_campaign_message_statuses');
    }
};
