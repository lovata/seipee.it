<?php

namespace Renatio\FormBuilder\Classes;

use Illuminate\Support\Facades\Validator;

class FormValidator
{
    protected $form;

    public function __construct($form)
    {
        $this->form = $form;
    }

    public function make()
    {
        return Validator::make(request()->all(), $this->rules(), $this->messages())
            ->setAttributeNames($this->names());
    }

    protected function rules()
    {
        return $this->form->fields
            ->filter(fn($field) => ! ! $field->validation_messages)
            ->mapWithKeys(fn($field) => $this->mapRules($field))
            ->all();
    }

    protected function messages()
    {
        return $this->form->fields
            ->filter(fn($field) => ! ! $field->validation_messages)
            ->map(fn($field) => $this->mapFieldToValidationMessages($field))
            ->collapse()
            ->all();
    }

    protected function names()
    {
        return $this->form->fields
            ->mapWithKeys(fn($field) => [
                $field->name => $field->label ?: ($field->placeholder ?: $field->name),
                $field->name.'.*' => $field->label ?: ($field->placeholder ?: $field->name),
            ])
            ->all();
    }

    protected function mapFieldToValidationMessages($field)
    {
        return collect($field->validation_messages)
            ->filter(fn($message) => $message['message'])
            ->mapWithKeys(fn($message) => [
                $field->name.(($message['is_array'] ?? null) ? '.*.' : '.').strtok($message['rule'], ':')
                => $message['message'],
            ]);
    }

    protected function mapRules($field)
    {
        return collect($field->validation_messages)
            ->mapToGroups(fn($validation) => [
                ($validation['is_array'] ?? null) ? $field->name.'.*' : $field->name => $validation['rule'],
            ]);
    }
}
