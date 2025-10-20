<?php

namespace Renatio\FormBuilder\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ImportExportController;
use Backend\Behaviors\ListController;
use Backend\Classes\Controller;
use Backend\Facades\BackendMenu;
use Illuminate\Database\Eloquent\Builder;
use October\Rain\Support\Facades\Flash;
use Renatio\FormBuilder\Models\FormLog;

class FormLogs extends Controller
{
    public $requiredPermissions = ['renatio.formbuilder.access_form_logs'];

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

        BackendMenu::setContext('Renatio.FormBuilder', 'formbuilder', 'formlogs');
    }

    public function index_onEmptyLog()
    {
        foreach (FormLog::with('form')->get(['id', 'form_id']) as $log) {
            $log->delete();
        }

        Flash::success(e(trans('renatio.formbuilder::lang.logs.empty_success')));

        return $this->listRefresh();
    }

    public function formExtendQuery(Builder $query)
    {
        $query->with('form.fields.field_type');
    }

    public function formExtendModel($model)
    {
        $model->extendWithUploadFields();
    }

    public function previewEmail($id)
    {
        return response($this->formFindModelObject($id)?->content_html);
    }

    public function formExtendFields($form)
    {
        if (! $form->model->form) {
            return;
        }

        foreach ($form->model->form->uploadFields() as $field) {
            $form->addTabFields([
                $field->name => [
                    'label' => $field->label,
                    'tab' => 'renatio.formbuilder::lang.tab.attachments',
                    'mode' => $field->file_mode,
                    'type' => 'fileupload',
                ],
            ]);
        }
    }
}
