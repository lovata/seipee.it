<?php

namespace Renatio\FormBuilder\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateFormsTable extends Migration
{
    public function up()
    {
        Schema::create('renatio_formbuilder_forms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->text('recipients')->nullable();
            $table->text('cc_recipients')->nullable();
            $table->text('bcc_recipients')->nullable();
            $table->text('success_message')->nullable();
            $table->text('error_message')->nullable();
            $table->string('css_class')->nullable();
            $table->string('reply_email')->nullable();
            $table->string('reply_name')->nullable();
            $table->string('response_email')->nullable();
            $table->string('response_name')->nullable();
            $table->string('redirect_to')->nullable();
            $table->string('template_code')->nullable();
            $table->string('response_template_code')->nullable();
            $table->text('markup')->nullable();
            $table->boolean('has_floating_labels')->default(false);
            $table->boolean('is_log_enabled')->default(true);
            $table->boolean('is_autoresponder_log_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('renatio_formbuilder_forms');
    }
}
