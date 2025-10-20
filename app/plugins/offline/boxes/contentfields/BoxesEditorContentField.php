<?php

namespace OFFLINE\Boxes\ContentFields;

use October\Contracts\Element\FormElement;
use OFFLINE\Boxes\Models\Content;
use Tailor\Classes\ContentFieldBase;

/**
 * BoxesEditorContentField makes the Boxes Editor usable in Tailor.
 *
 * @see https://docs.boxes.offline.ch/use-cases/tailor.html
 */
class BoxesEditorContentField extends ContentFieldBase
{
    public function defineFormField(FormElement $form, $context = null)
    {
        $form
            ->addFormField($this->fieldName, $this->label)
            ->useConfig($this->config);
    }

    public function extendModelObject($model)
    {
        $model->morphOne[$this->fieldName] = [
            Content::class,
            'name' => 'content',
            'replicate' => true,
        ];
    }
}
