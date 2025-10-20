<?php

namespace Renatio\FormBuilder\Updates;

use October\Rain\Database\Updates\Seeder;
use Renatio\FormBuilder\Models\FieldType;
use Renatio\FormBuilder\Models\Form;

class SeedDefaultForm extends Seeder
{
    public function run()
    {
        $form = $this->createForm();

        $fieldTypes = FieldType::get(['id', 'code']);

        foreach ($this->fields() as $field) {
            $type = $field['type'];
            unset($field['type']);

            $form->fields()->create(
                array_merge([
                    'field_type_id' => $fieldTypes->where('code', $type)->first()->id,
                ], $field)
            );
        }
    }

    protected function createForm()
    {
        return Form::create([
            'template_code' => 'renatio.formbuilder::mail.default',
            'name' => 'Default Form',
            'description' => 'Renders a form with all available system fields.',
            'success_message' => e(trans('renatio.formbuilder::lang.message.success')),
            'error_message' => e(trans('renatio.formbuilder::lang.message.error')),
            'recipients' => [
                [
                    'email' => 'admin@domain.tld',
                    'recipient_name' => 'Admin Person',
                ],
            ],
            'css_class' => 'row g-3',
        ]);
    }

    protected function fields()
    {
        return [
            [
                'type' => 'text',
                'label' => 'Text',
                'name' => 'text',
                'validation_messages' => [
                    [
                        'rule' => 'required',
                        'message' => '',
                    ],
                ],
                'wrapper_class' => 'col-md-6',
            ],
            [
                'type' => 'email',
                'label' => 'E-mail',
                'name' => 'email',
                'validation_messages' => [
                    [
                        'rule' => 'required',
                        'message' => '',
                    ],
                    [
                        'rule' => 'email',
                        'message' => '',
                    ],
                ],
                'wrapper_class' => 'col-md-6',
            ],
            [
                'type' => 'phone',
                'label' => 'Phone',
                'name' => 'phone',
                'validation_messages' => [
                    [
                        'rule' => 'required',
                        'message' => '',
                    ],
                ],
                'wrapper_class' => 'col-md-6',
            ],
            [
                'type' => 'url',
                'label' => 'URL',
                'name' => 'url',
                'validation_messages' => [
                    [
                        'rule' => 'required',
                        'message' => '',
                    ],
                ],
                'wrapper_class' => 'col-md-6',
            ],
            [
                'type' => 'numeric',
                'label' => 'Numeric',
                'name' => 'numeric',
                'validation_messages' => [
                    [
                        'rule' => 'required',
                        'message' => '',
                    ],
                    [
                        'rule' => 'numeric',
                        'message' => '',
                    ],
                ],
                'wrapper_class' => 'col-md-6',
            ],
            [
                'type' => 'datetime',
                'label' => 'Datetime',
                'name' => 'datetime',
                'validation_messages' => [
                    [
                        'rule' => 'required',
                        'message' => '',
                    ],
                ],
                'wrapper_class' => 'col-md-6',
            ],
            [
                'type' => 'date',
                'label' => 'Date',
                'name' => 'date',
                'validation_messages' => [
                    [
                        'rule' => 'required',
                        'message' => '',
                    ],
                ],
                'wrapper_class' => 'col-md-6',
            ],
            [
                'type' => 'time',
                'label' => 'Time',
                'name' => 'time',
                'validation_messages' => [
                    [
                        'rule' => 'required',
                        'message' => '',
                    ],
                ],
                'wrapper_class' => 'col-md-6',
            ],
            [
                'type' => 'dropdown',
                'label' => 'Dropdown',
                'name' => 'dropdown',
                'options' => [
                    '1' => [
                        'o_key' => 'option_1',
                        'o_label' => 'Option 1',
                    ],
                    '2' => [
                        'o_key' => 'option_2',
                        'o_label' => 'Option 2',
                    ],
                ],
                'placeholder' => '-- choose --',
                'validation_messages' => [
                    [
                        'rule' => 'required',
                        'message' => '',
                    ],
                ],
                'wrapper_class' => 'col-md-12',
            ],
            [
                'type' => 'checkbox',
                'label' => 'Checkbox',
                'name' => 'checkbox',
                'validation_messages' => [
                    [
                        'rule' => 'required',
                        'message' => '',
                    ],
                ],
                'wrapper_class' => 'col-md-6',
            ],
            [
                'type' => 'color_picker',
                'label' => 'Color picker',
                'name' => 'color',
                'wrapper_class' => 'col-md-6',
            ],
            [
                'type' => 'checkbox_list',
                'label' => 'Checkbox list',
                'name' => 'checkbox_list',
                'options' => [
                    '1' => [
                        'o_key' => 'checkbox_option_1',
                        'o_label' => 'Option 1',
                    ],
                    '2' => [
                        'o_key' => 'checkbox_option_2',
                        'o_label' => 'Option 2',
                    ],
                ],
                'validation_messages' => [
                    [
                        'rule' => 'required',
                        'message' => '',
                    ],
                ],
                'wrapper_class' => 'col-md-6',
            ],
            [
                'type' => 'radio_list',
                'label' => 'Radio list',
                'name' => 'radio_list',
                'options' => [
                    '1' => [
                        'o_key' => 'radio_option_1',
                        'o_label' => 'Option 1',
                    ],
                    '2' => [
                        'o_key' => 'radio_option_2',
                        'o_label' => 'Option 2',
                    ],
                ],
                'validation_messages' => [
                    [
                        'rule' => 'required',
                        'message' => '',
                    ],
                ],
                'wrapper_class' => 'col-md-6',
            ],
            [
                'type' => 'country_select',
                'label' => 'Country select',
                'name' => 'country',
                'validation_messages' => [
                    [
                        'rule' => 'required',
                        'message' => '',
                    ],
                ],
                'placeholder' => '-- choose --',
                'wrapper_class' => 'col-md-6',
            ],
            [
                'type' => 'state_select',
                'label' => 'State select',
                'name' => 'state',
                'validation_messages' => [
                    [
                        'rule' => 'required',
                        'message' => '',
                    ],
                ],
                'placeholder' => '-- choose --',
                'wrapper_class' => 'col-md-6',
            ],
            [
                'type' => 'textarea',
                'label' => 'Textarea',
                'name' => 'textarea',
                'validation_messages' => [
                    [
                        'rule' => 'required',
                        'message' => '',
                    ],
                ],
                'wrapper_class' => 'col-md-12',
            ],
            [
                'type' => 'section',
                'label' => 'Files Section',
                'name' => 'section',
                'wrapper_class' => 'col-md-12',
            ],
            [
                'type' => 'upload',
                'label' => 'Files',
                'name' => 'files',
                'wrapper_class' => 'col-md-6',
                'validation_messages' => [
                    [
                        'rule' => 'required',
                        'message' => '',
                    ],
                    [
                        'rule' => 'max:512',
                        'message' => '',
                        'is_array' => true,
                    ],
                    [
                        'rule' => 'mimes:pdf',
                        'message' => '',
                        'is_array' => true,
                    ],
                ],
                'comment' => 'Only .pdf files are allowed.',
                'custom_attributes' => 'accept=".pdf"',
                'has_multiple_files' => true,
                'file_mode' => 'file',
            ],
            [
                'type' => 'upload',
                'label' => 'Images',
                'name' => 'images',
                'wrapper_class' => 'col-md-6',
                'validation_messages' => [
                    [
                        'rule' => 'required',
                        'message' => '',
                    ],
                    [
                        'rule' => 'max:512',
                        'message' => '',
                        'is_array' => true,
                    ],
                    [
                        'rule' => 'image',
                        'message' => '',
                        'is_array' => true,
                    ],
                ],
                'comment' => 'Only image files are allowed.',
                'custom_attributes' => 'accept="image/*"',
                'has_multiple_files' => true,
                'file_mode' => 'image',
            ],
            [
                'type' => 'recaptcha',
                'label' => 'reCaptcha',
                'name' => 'g-recaptcha-response',
                'validation_messages' => [
                    [
                        'rule' => 'required',
                        'message' => '',
                    ],
                    [
                        'rule' => 'recaptcha',
                        'message' => '',
                    ],
                ],
                'wrapper_class' => 'col-md-12',
            ],
            [
                'type' => 'hidden',
                'label' => 'Hidden',
                'name' => 'hidden',
                'default' => 'hidden',
            ],
            [
                'type' => 'submit',
                'label' => 'Send',
                'name' => 'submit',
                'wrapper_class' => 'col-md-12 text-center',
            ],
        ];
    }
}
