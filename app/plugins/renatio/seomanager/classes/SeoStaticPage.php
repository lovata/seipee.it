<?php

namespace Renatio\SeoManager\Classes;

use Backend\Widgets\Form;
use Facades\Renatio\SeoManager\Classes\SeoFields;
use October\Rain\Support\Facades\Event;
use RainLab\Pages\Classes\Page;

class SeoStaticPage
{
    public function extend()
    {
        $this->addSeoFields();
    }

    protected function addSeoFields()
    {
        Event::listen('backend.form.extendFieldsBefore', function (Form $widget) {
            if (! $widget->model instanceof Page) {
                return;
            }

            /*
             * Handle infinite loop
             */
            if ($widget->isNested) {
                return;
            }

            $widget->getController()->addJs('/plugins/renatio/seomanager/assets/js/main.js');

            $this->addTranslatableFields($widget->model);

            $this->addFields($widget);
        });
    }

    protected function addTranslatableFields($model)
    {
        foreach ($model->translatable as $key => $field) {
            if (in_array($field, ['viewBag[meta_title]', 'viewBag[meta_description]'])) {
                unset($model->translatable[$key]);
            }
        }

        $model->translatable = array_merge($model->translatable ?? [], $this->translatableFields());
    }

    protected function addFields(Form $widget)
    {
        $widget->tabs['fields'] = array_merge($widget->tabs['fields'] ?? [], $this->fields());
    }

    protected function fields()
    {
        return collect(SeoFields::fields())
            ->mapWithKeys(function ($field, $key) {
                return ['viewBag['.$key.']' => $field];
            })
            ->all();
    }

    protected function translatableFields()
    {
        return collect(SeoFields::translatable())
            ->map(function ($key) {
                return 'viewBag['.$key.']';
            })
            ->all();
    }
}
