<?php

namespace Renatio\FormBuilder\Updates;

use Illuminate\Support\Facades\File;
use October\Rain\Database\Updates\Migration;
use Renatio\FormBuilder\Models\FieldType;

class AddHiddenField extends Migration
{
    public function up()
    {
        if (FieldType::where('code', 'hidden')->exists()) {
            return;
        }

        FieldType::create([
            'name' => 'Hidden',
            'code' => 'hidden',
            'description' => 'Renders a hidden field.',
            'markup' => File::get(__DIR__.'/fields/_hidden.htm'),
            'is_default' => 1,
        ]);
    }

    public function down()
    {
        //
    }
}
