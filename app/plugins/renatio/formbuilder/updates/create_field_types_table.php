<?php

namespace Renatio\FormBuilder\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateFieldTypesTable extends Migration
{
    public function up()
    {
        Schema::create('renatio_formbuilder_field_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('code')->unique();
            $table->text('markup')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('renatio_formbuilder_field_types');
    }
}
