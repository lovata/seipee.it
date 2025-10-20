<?php

namespace Renatio\FormBuilder\Models;

use Illuminate\Database\Eloquent\Prunable;
use October\Rain\Database\Model;
use System\Models\File;

class FormLog extends Model
{
    use Prunable;

    public $table = 'renatio_formbuilder_form_logs';

    public $belongsTo = [
        'form' => Form::class,
    ];

    protected $jsonable = ['form_data'];

    public static function saveForm($form)
    {
        $log = new static;

        $log->form_id = $form->id;
        $log->form_data = $form->getData();

        foreach ($form->uploadFields() as $field) {
            $log->{$field->name} = files($field->name);
        }

        $log->save();

        return $log;
    }

    public function afterDelete()
    {
        if (! $this->form) {
            return;
        }

        $this->extendWithUploadFields();

        foreach ($this->form->uploadFields() as $field) {
            if ($this->{$field->name} instanceof File) {
                $this->{$field->name}->delete();
            } else {
                $this->{$field->name}->each->delete();
            }
        }
    }

    public function extendWithUploadFields()
    {
        if (! $this->form) {
            return;
        }

        foreach ($this->form->uploadFields() as $field) {
            $attachType = $field->has_multiple_files ? 'attachMany' : 'attachOne';

            $this->$attachType[$field->name] = [
                File::class,
            ];
        }
    }

    public function prunable()
    {
        if (! ($prunePeriod = (int) Settings::get('prune_logs_period'))) {
            exit;
        }

        return static::where('created_at', '<=', now()->subDays($prunePeriod));
    }
}
