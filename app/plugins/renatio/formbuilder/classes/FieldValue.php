<?php

namespace Renatio\FormBuilder\Classes;

use RainLab\Location\Models\Country;
use RainLab\Location\Models\State;

class FieldValue
{
    public function get($field)
    {
        $value = post($field->name);

        if (is_array($value)) {
            return $this->getArrayValue($value, $field);
        }

        return match ($field->field_type->code) {
            'dropdown', 'radio_list' => $field->list_options[$value],
            'checkbox' => $value ? e(trans('renatio.formbuilder::lang.checkbox.true')) : e(trans('renatio.formbuilder::lang.checkbox.false')),
            'country_select' => $this->getCountryName($value),
            'state_select' => $this->getStateName($value),
            default => $value,
        };
    }

    protected function getCountryName($countryId)
    {
        return class_exists(Country::class) ? Country::find($countryId)?->name : null;
    }

    protected function getStateName($stateId)
    {
        return class_exists(State::class) ? State::find($stateId)?->name : null;
    }

    protected function getArrayValue($array, $field)
    {
        return collect($array)
            ->map(fn($value) => $field->list_options[$value])
            ->implode(', ');
    }
}
