<?php

namespace Renatio\FormBuilder\Models;

use Backend\Models\ExportModel;

class FormLogExport extends ExportModel
{
    public $table = 'renatio_formbuilder_form_logs';

    public function exportData($columns, $sessionKey = null)
    {
        return static::get()->toArray();
    }

    public function getFormDataAttribute()
    {
        return post('file_format') === 'json'
            ? json_decode($this->attributes['form_data'], true)
            : $this->attributes['form_data'];
    }
}
