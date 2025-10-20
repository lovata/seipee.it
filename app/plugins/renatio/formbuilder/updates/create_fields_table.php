<?php

namespace Renatio\FormBuilder\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateFieldsTable extends Migration
{
    public function up()
    {
        Schema::create('renatio_formbuilder_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('form_id')->index()->nullable();
            $table->unsignedInteger('field_type_id')->index();
            $table->string('label')->nullable();
            $table->string('name');
            $table->string('default')->nullable();
            $table->string('class')->nullable();
            $table->string('placeholder')->nullable();
            $table->string('custom_attributes')->nullable();
            $table->text('options')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->text('comment')->nullable();
            $table->string('wrapper_class')->nullable();
            $table->text('validation_messages')->nullable();
            $table->string('label_class')->nullable();
            $table->unsignedInteger('sort_order')->index()->nullable();
            $table->boolean('has_multiple_files')->default(false);
            $table->string('file_mode')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('renatio_formbuilder_fields');
    }
}
