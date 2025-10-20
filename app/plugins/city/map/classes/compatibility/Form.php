<?php namespace City\Map\Classes\Compatibility;

class Form
{
    public static function fixFields($form)
    {
        $compatibility = new Compatibility;
        if (! $compatibility->apply()) {
            return;
        }

        foreach ($form->getFields() as $field) {
            if ('ruler' === $field->type) {
                $form->removeField($field->fieldName);
            } elseif ('hint' === $field->type) {
                $form->removeField($field->fieldName);
            }
        }
    }
}
