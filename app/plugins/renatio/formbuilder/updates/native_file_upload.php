<?php

namespace Renatio\FormBuilder\Updates;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Renatio\FormBuilder\Models\Field;
use Renatio\FormBuilder\Models\FieldType;
use Schema;

class NativeFileUpload extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('renatio_formbuilder_fields', 'has_multiple_files')) {
            Schema::table('renatio_formbuilder_fields', function (Blueprint $table) {
                $table->boolean('has_multiple_files')->default(false);
                $table->string('file_mode')->nullable();
            });
        }

        if (! Schema::hasColumn('renatio_formbuilder_fields', 'max_size')) {
            return;
        }

        $uploadFieldType = FieldType::create([
            'name' => 'File upload',
            'code' => 'upload',
            'description' => 'Renders a file upload field.',
            'markup' => File::get(__DIR__.'/fields/_upload.htm'),
            'is_default' => 1,
        ]);

        $fileUploadFields = Field::query()
            ->whereHas('field_type', fn(Builder $q) => $q->whereIn('code', ['file_uploader', 'image_uploader']))
            ->get();

        foreach ($fileUploadFields as $fileUploadField) {
            $fileUploadField->update([
                'field_type_id' => $uploadFieldType->id,
                'file_mode' => $fileUploadField->field_type->code === 'file_uploader' ? 'file' : 'image',
                'has_multiple_files' => 1,
                'validation_messages' => $this->getValidationMessages($fileUploadField),
            ]);
        }

        FieldType::query()
            ->whereIn('code', ['file_uploader', 'image_uploader'])
            ->delete();
    }

    public function down()
    {
        Schema::table('renatio_formbuilder_fields', function (Blueprint $table) {
            $table->dropColumn(['has_multiple_files', 'file_mode']);
        });
    }

    protected function getValidationMessages($field)
    {
        $optionRules = [];

        if ($field->max_size) {
            $optionRules[] = [
                'rule' => 'max:'.$field->max_size * 1024,
                'message' => '',
            ];
        }

        if ($field->file_types && $field->file_types !== '*') {
            $optionRules[] = [
                'rule' => 'mimes:'.str_replace('.', '', $field->file_types),
                'message' => '',
            ];
        }

        return array_merge((array) $field->validation_messages, $optionRules);
    }
}
