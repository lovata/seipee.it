<?php

namespace Renatio\FormBuilder\Models;

use Bkwld\Cloner\Cloneable;
use Illuminate\Validation\Rule;
use October\Rain\Database\Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Validation;
use October\Rain\Support\Facades\File;
use RainLab\Translate\Behaviors\TranslatableModel;

class FieldType extends Model
{
    use Validation;
    use Sluggable;
    use Cloneable;

    public $implement = ['@'.TranslatableModel::class];

    public $table = 'renatio_formbuilder_field_types';

    public $rules = [
        'name' => ['required'],
        'code' => ['required'],
    ];

    protected $slugs = ['code' => 'name'];

    public $translatable = ['name', 'description'];

    protected $clone_exempt_attributes = ['is_default'];

    public function restoreMarkupToDefault()
    {
        if (! ($markup = $this->getDefaultMarkup())) {
            return;
        }

        $this->markup = $markup;

        $this->forceSave();
    }

    public function beforeDelete()
    {
        if ($this->is_default) {
            return false;
        }
    }

    public function beforeValidate()
    {
        if ($this->exists) {
            $this->rules['code'] = ['required', Rule::unique('renatio_formbuilder_field_types')->ignore($this->id)];
        }
    }

    public function filterFields($fields, $context)
    {
        if ($context == 'update' && $this->is_default) {
            $fields->code->disabled = true;
        }
    }

    protected function getDefaultMarkup()
    {
        $path = __DIR__."/../updates/fields/_{$this->code}.htm";

        if (! File::exists($path)) {
            return false;
        }

        return File::get($path);
    }
}
