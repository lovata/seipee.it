<?php

namespace Renatio\FormBuilder\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateFormLogsTable extends Migration
{
    public function up()
    {
        Schema::create('renatio_formbuilder_form_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('form_id')->index();
            $table->text('form_data')->nullable();
            $table->text('content_html')->nullable();
            $table->string('subject')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('cc')->nullable();
            $table->string('bcc')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('renatio_formbuilder_form_logs');
    }
}
