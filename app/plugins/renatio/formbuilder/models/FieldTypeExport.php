<?php

namespace Renatio\FormBuilder\Models;

use Backend\Models\ExportModel;

class FieldTypeExport extends ExportModel
{
    public $table = 'renatio_formbuilder_field_types';

    public function exportData($columns, $sessionKey = null)
    {
        return static::query()
            ->when(get('checked'), fn($query) => $query->whereIn('id', get('checked')))
            ->get()
            ->toArray();
    }
}
