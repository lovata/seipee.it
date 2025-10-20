<?php

namespace Renatio\FormBuilder\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ImportExportController;
use Backend\Behaviors\ListController;
use Backend\Behaviors\RelationController;
use Backend\Classes\Controller;
use Backend\Facades\Backend;
use Backend\Facades\BackendMenu;
use October\Rain\Support\Facades\Flash;
use Renatio\FormBuilder\Models\Field;

class Forms extends Controller
{
    public $requiredPermissions = ['renatio.formbuilder.access_forms'];

    public $implement = [
        ListController::class,
        FormController::class,
        RelationController::class,
        ImportExportController::class,
    ];

    public $listConfig = 'config_list.yaml';

    public $formConfig = 'config_form.yaml';

    public $relationConfig = 'config_relation.yaml';

    public $importExportConfig = 'config_import_export.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Renatio.FormBuilder', 'formbuilder', 'forms');
    }

    public function onDuplicate()
    {
        $form = $this->formFindModelObject(post('id'));

        $form->duplicate();

        Flash::success(e(trans('renatio.formbuilder::lang.form.duplicate_success')));

        return $this->listRefresh();
    }

    public function onToggleFieldVisibility()
    {
        $field = Field::find(post('fieldId'));

        if (! $field) {
            return;
        }

        $field->is_visible = ! $field->is_visible;

        $field->forceSave();
    }

    public function onExportSelected()
    {
        return Backend::redirect('renatio/formbuilder/forms/export?'.http_build_query(['checked' => post('checked')]));
    }
}
