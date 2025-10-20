<?php

namespace Renatio\FormBuilder\Updates;

use Illuminate\Support\Facades\File;
use October\Rain\Database\Updates\Seeder;
use Renatio\FormBuilder\Models\FieldType;

class SeedFieldTypesTable extends Seeder
{
    public function run()
    {
        $path = __DIR__.'/fields/';

        FieldType::create([
            'name' => 'Text',
            'code' => 'text',
            'description' => 'Renders a single line text box.',
            'markup' => File::get($path.'_text.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'Textarea',
            'code' => 'textarea',
            'description' => 'Renders a multiline text box.',
            'markup' => File::get($path.'_textarea.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'Dropdown',
            'code' => 'dropdown',
            'description' => 'Renders a dropdown with specified options.',
            'markup' => File::get($path.'_dropdown.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'Checkbox',
            'code' => 'checkbox',
            'description' => 'Renders a single checkbox.',
            'markup' => File::get($path.'_checkbox.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'Checkbox List',
            'code' => 'checkbox_list',
            'description' => 'Renders a list of checkboxes.',
            'markup' => File::get($path.'_checkbox_list.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'Radio List',
            'code' => 'radio_list',
            'description' => 'Renders a list of radio options, where only one item can be selected at a time.',
            'markup' => File::get($path.'_radio_list.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'ReCaptcha',
            'code' => 'recaptcha',
            'description' => 'Renders a reCaptcha box.',
            'markup' => File::get($path.'_recaptcha.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'Submit',
            'code' => 'submit',
            'description' => 'Renders a submit button.',
            'markup' => File::get($path.'_submit.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'Country select',
            'code' => 'country_select',
            'description' => 'Renders a dropdown with country options.',
            'markup' => File::get($path.'_country_select.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'State select',
            'code' => 'state_select',
            'description' => 'Renders a dropdown with state options.',
            'markup' => File::get($path.'_state_select.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'File upload',
            'code' => 'upload',
            'description' => 'Renders a file upload field.',
            'markup' => File::get($path.'_upload.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'Section',
            'code' => 'section',
            'description' => 'Renders a section heading and subheading.',
            'markup' => File::get($path.'_section.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'E-mail',
            'code' => 'email',
            'description' => 'Renders e-mail address field.',
            'markup' => File::get($path.'_email.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'Phone number',
            'code' => 'phone',
            'description' => 'Renders phone number field.',
            'markup' => File::get($path.'_phone.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'URL',
            'code' => 'url',
            'description' => 'Renders URL field.',
            'markup' => File::get($path.'_url.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'Numeric',
            'code' => 'numeric',
            'description' => 'Renders numeric field.',
            'markup' => File::get($path.'_numeric.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'Datetime',
            'code' => 'datetime',
            'description' => 'Renders datetime field.',
            'markup' => File::get($path.'_datetime.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'Date',
            'code' => 'date',
            'description' => 'Renders date field.',
            'markup' => File::get($path.'_date.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'Time',
            'code' => 'time',
            'description' => 'Renders time field.',
            'markup' => File::get($path.'_time.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'Color Picker',
            'code' => 'color_picker',
            'description' => 'Renders a color picker.',
            'markup' => File::get($path.'_color_picker.htm'),
            'is_default' => 1,
        ]);

        FieldType::create([
            'name' => 'Hidden',
            'code' => 'hidden',
            'description' => 'Renders a hidden field.',
            'markup' => File::get($path.'_hidden.htm'),
            'is_default' => 1,
        ]);
    }
}
