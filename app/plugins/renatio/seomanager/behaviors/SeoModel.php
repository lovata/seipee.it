<?php

namespace Renatio\SeoManager\Behaviors;

use Facades\Renatio\SeoManager\Classes\SeoFields;
use October\Rain\Database\Model;
use October\Rain\Support\Facades\Event;
use System\Classes\ModelBehavior;
use System\Classes\PluginManager;

class SeoModel extends ModelBehavior
{
    public function __construct($model)
    {
        parent::__construct($model);

        $this->addTranslatableFields();

        $this->extendFields();
    }

    protected function addTranslatableFields()
    {
        if (! PluginManager::instance()->exists('RainLab.Translate')) {
            return;
        }

        $this->model->translatable = array_merge($this->model->translatable ?? [], SeoFields::translatable());
    }

    protected function extendFields()
    {
        Event::listen('backend.form.extendFieldsBefore', function ($widget) {
            if (! $this->hasSeoModelBehavior(optional($widget)->model)) {
                return;
            }

            /*
             * Handle infinite loop
             */
            if ($widget->isNested) {
                return;
            }

            /*
             * Handle pivot models
             */
            if (array_key_exists('pivot', $widget->model->getRelations())) {
                return;
            }

            $widget->getController()->addJs('/plugins/renatio/seomanager/assets/js/main.js');

            $this->addFields($widget);
        });
    }

    protected function addFields($widget)
    {
        if ($this->tab() == 'secondary') {
            $widget->secondaryTabs['fields'] = array_merge($widget->secondaryTabs['fields'] ?? [], SeoFields::fields());
        } else {
            $widget->tabs['fields'] = array_merge($widget->tabs['fields'] ?? [], SeoFields::fields());
        }
    }

    protected function tab()
    {
        return $this->model->methodExists('getSeoTab') ? $this->model->getSeoTab() : 'primary';
    }

    protected function hasSeoModelBehavior($model)
    {
        return $model instanceof Model
            && $model->isClassExtendedWith(static::class);
    }
}
