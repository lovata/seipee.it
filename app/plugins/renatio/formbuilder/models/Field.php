<?php

namespace Renatio\FormBuilder\Models;

use October\Rain\Database\Model;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use October\Rain\Support\Facades\Twig;
use RainLab\Translate\Behaviors\TranslatableModel;
use Renatio\FormBuilder\Classes\FieldValue;
use System\Classes\PluginManager;

class Field extends Model
{
    use Validation;
    use Sortable;

    public $implement = ['@'.TranslatableModel::class];

    public $table = 'renatio_formbuilder_fields';

    public $rules = [
        'field_type' => ['required'],
        'name' => ['required'],
        'validation_messages.*.rule' => ['required'],
    ];

    public $attributeNames = [
        'validation_messages.*.rule' => 'renatio.formbuilder::lang.field.rule',
    ];

    public $translatable = ['label', 'default', 'placeholder', 'comment', 'options', 'validation_messages'];

    protected $jsonable = ['options', 'validation_messages'];

    protected $with = ['form'];

    public $belongsTo = [
        'form' => Form::class,
        'field_type' => [
            FieldType::class,
            'order' => 'name asc',
        ],
    ];

    public function scopeIsVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function getHtmlAttribute()
    {
        return Twig::parse($this->field_type->markup, $this->prepareFieldOptions());
    }

    public function getValueAttribute()
    {
        return (new FieldValue)->get($this);
    }

    public function getFormValueAttribute()
    {
        return post($this->name);
    }

    public function getListOptionsAttribute()
    {
        return collect($this->options)->lists('o_label', 'o_key');
    }

    public function getIsViewableAttribute()
    {
        return ! in_array($this->field_type->code, [
            'recaptcha',
            'submit',
            'upload',
            'section',
        ]);
    }

    public function getValidationAttribute()
    {
        return $this->validation ?? collect($this->validation_messages)->implode('rule', '|');
    }

    public function filterFields($fields, $context = null)
    {
        if ($this->field_type?->code === 'upload' && ! empty($fields->has_multiple_files)) {
            $fields->has_multiple_files->hidden = false;
            $fields->send_file_as_mail_attachment->hidden = false;
            $fields->file_mode->hidden = false;
        }
    }

    protected function prepareFieldOptions()
    {
        return [
            'field' => $this,
            'field_id' => $this->field_id,
            'label' => $this->label,
            'label_class' => $this->label_class,
            'name' => $this->name,
            'default' => $this->default,
            'comment' => $this->comment,
            'class' => $this->class,
            'wrapper_class' => $this->wrapper_class,
            'placeholder' => $this->placeholder,
            'options' => $this->list_options,
            'custom_attributes' => $this->custom_attributes,
            'settings' => Settings::instance(),
            'location_plugin_enabled' => PluginManager::instance()->exists('RainLab.Location'),
            'has_floating_labels' => $this->form->has_floating_labels,
            'has_multiple_files' => $this->has_multiple_files,
        ];
    }
}
