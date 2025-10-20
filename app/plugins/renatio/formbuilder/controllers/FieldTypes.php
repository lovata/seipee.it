<?php

namespace Renatio\FormBuilder\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ImportExportController;
use Backend\Behaviors\ListController;
use Backend\Classes\Controller;
use Backend\Facades\Backend;
use Backend\Facades\BackendMenu;
use Illuminate\Database\Eloquent\Builder;
use October\Rain\Support\Facades\Flash;
use Renatio\FormBuilder\Models\Field;
use Renatio\FormBuilder\Models\FieldType;

class FieldTypes extends Controller
{
    public $requiredPermissions = ['renatio.formbuilder.access_field_types'];

    public $implement = [
        ListController::class,
        FormController::class,
        ImportExportController::class,
    ];

    public $listConfig = 'config_list.yaml';

    public $formConfig = 'config_form.yaml';

    public $importExportConfig = 'config_import_export.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Renatio.FormBuilder', 'formbuilder', 'fieldtypes');
    }

    public function index_onDelete()
    {
        $fields = Field::query()
            ->whereHas('field_type', function (Builder $query) {
                $query->whereIn('id', post('checked'));
            })
            ->get();

        if (! $fields->isEmpty()) {
            Flash::error(e(trans('renatio.formbuilder::lang.field_type.delete_error')));

            return;
        }

        $this->asExtension('ListController')->index_onDelete();

        return $this->listRefresh();
    }

    public function onDuplicate()
    {
        $field = $this->formFindModelObject(post('id'));

        $field->duplicate();

        Flash::success(e(trans('renatio.formbuilder::lang.field_type.duplicate_success')));

        return $this->listRefresh();
    }

    public function onRestoreAllFields()
    {
        $fields = FieldType::where('is_default', true)->get();

        foreach ($fields as $field) {
            $field->restoreMarkupToDefault();
        }

        Flash::success(e(trans('renatio.formbuilder::lang.field_type.restore_success')));

        return $this->listRefresh();
    }

    public function onRestore($id)
    {
        $field = $this->formFindModelObject($id);

        $field->restoreMarkupToDefault();

        Flash::success(e(trans('renatio.formbuilder::lang.field_type.restore_success')));

        return redirect()->refresh();
    }

    public function onExportSelected()
    {
        return Backend::redirect('renatio/formbuilder/fieldtypes/export?'.http_build_query(['checked' => post('checked')]));
    }
}
