<?php

namespace Renatio\FormBuilder\Models;

use Backend\Models\ImportModel;
use Carbon\Carbon;

class FieldTypeImport extends ImportModel
{
    public $table = 'renatio_formbuilder_field_types';

    public $rules = [
        'name' => ['required'],
        'code' => ['required'],
    ];

    public function importData($results, $sessionKey = null)
    {
        foreach ($results as $row => $data) {
            try {
                $fieldType = FieldType::where('code', array_get($data, 'code'))->first() ?? FieldType::make();

                $exists = $fieldType->exists;

                foreach (array_except($data, ['id']) as $attribute => $value) {
                    if (in_array($attribute, ['created_at', 'updated_at'])) {
                        $fieldType->$attribute = Carbon::parse($value);
                    } else {
                        $fieldType->$attribute = $value;
                    }
                }

                $fieldType->forceSave();

                if ($exists) {
                    $this->logUpdated();
                } else {
                    $this->logCreated();
                }
            } catch (\Exception $ex) {
                $this->logError($row, $ex->getMessage());
            }
        }
    }
}
