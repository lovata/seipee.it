<?php

namespace Renatio\FormBuilder\Models;

use Bkwld\Cloner\Cloneable;
use Illuminate\Support\Facades\DB;
use October\Rain\Database\Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Validation;
use RainLab\Translate\Behaviors\TranslatableModel;
use System\Models\MailSetting;

class Form extends Model
{
    use Validation;
    use Sluggable;
    use Cloneable;

    public $implement = ['@'.TranslatableModel::class];

    public $table = 'renatio_formbuilder_forms';

    public $rules = [
        'name' => ['required'],
        'from_email' => ['email', 'nullable'],
        'recipients.*.email' => ['required', 'email'],
        'cc_recipients.*.email' => ['required', 'email'],
        'bcc_recipients.*.email' => ['required', 'email'],
    ];

    public $attributeNames = [
        'recipients.*.email' => 'renatio.formbuilder::lang.field.email',
        'cc_recipients.*.email' => 'renatio.formbuilder::lang.field.email',
        'bcc_recipients.*.email' => 'renatio.formbuilder::lang.field.email',
    ];

    public $translatable = [
        'name',
        'from_email',
        'from_name',
        'description',
        'recipients',
        'cc_recipients',
        'bcc_recipients',
        'success_message',
        'error_message',
        'redirect_to',
    ];

    protected $slugs = ['code' => 'name'];

    protected $jsonable = ['recipients', 'cc_recipients', 'bcc_recipients'];

    public $hasMany = [
        'fields' => [
            Field::class,
            'order' => 'sort_order',
        ],
    ];

    protected $cloneable_relations = ['fields'];

    public function beforeSave()
    {
        $this->from_email = $this->from_email ?: MailSetting::get('sender_email');
        $this->from_name = $this->from_name ?: MailSetting::get('sender_name');
    }

    public function afterDelete()
    {
        $this->fields->each->delete();
    }

    public function getFieldsOptions()
    {
        return $this->fields->lists('name', 'name');
    }

    public function getData()
    {
        return $this->fields
            ->filter(fn($field) => $field->is_viewable)
            ->map(fn($field) => [
                'name' => $field->name,
                'label' => $field->label,
                'placeholder' => $field->placeholder,
                'value' => $field->value,
            ]);
    }

    public function getDataArray()
    {
        return $this->getData()
            ->mapWithKeys(fn($record) => [$record['name'] => $record['value']])
            ->all();
    }

    public function uploadFields()
    {
        return $this->fields->filter(
            fn($field) => $field->field_type->code === 'upload'
        );
    }

    public function listMailTemplates()
    {
        return DB::table('system_mail_templates')
            ->orderBy('description')
            ->lists('description', 'code');
    }

    public function getMarkup()
    {
        return $this->markup ?: $this->fields->reduce(
            fn($markup, $field) => $markup."{{ form_field('$field->name') }}\n"
        );
    }
}
