<?php

namespace Renatio\FormBuilder\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class AddSendFileAsMailAttachmentField extends Migration
{
    public function up()
    {
        Schema::table('renatio_formbuilder_fields', function (Blueprint $table) {
            $table->boolean('send_file_as_mail_attachment')->default(true);
        });
    }

    public function down()
    {
        Schema::table('renatio_formbuilder_fields', function (Blueprint $table) {
            $table->dropColumn('send_file_as_mail_attachment');
        });
    }
}
