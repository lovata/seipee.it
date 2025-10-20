<?php namespace Responsiv\Campaign\Controllers;

use Lang;
use Flash;
use BackendMenu;
use Backend\Classes\Controller;
use Responsiv\Campaign\Models\Subscriber;

/**
 * Subscribers Back-end Controller
 */
class Subscribers extends Controller
{
    /**
     * @var array implement extensions
     */
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\ImportExportController::class
    ];

    /**
     * @var array formConfig configuration.
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var array listConfig configuration.
     */
    public $listConfig = 'config_list.yaml';

    /**
     * @var array importExportConfig configuration.
     */
    public $importExportConfig = 'config_import_export.yaml';

    /**
     * @var array requiredPermissions to view this page.
     */
    public $requiredPermissions = ['responsiv.campaign.manage_subscribers'];

    /**
     * __construct
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Responsiv.Campaign', 'campaign', 'subscribers');
    }

    /**
     * index_onDelete
     */
    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $recordId) {
                if (!$record = Subscriber::find($recordId)) continue;
                $record->delete();
            }

            Flash::success(Lang::get('backend::lang.list.delete_selected_success'));
        }
        else {
            Flash::error(Lang::get('backend::lang.list.delete_selected_empty'));
        }

        return $this->listRefresh();
    }

    /**
     * listInjectRowClass
     */
    public function listInjectRowClass($record)
    {
        if ($record->unsubscribed_at) {
            return 'negative';
        }

        if ($record->confirmed_at) {
            return 'positive';
        }
    }

    /**
     * update_onConfirm confirms a subscriber manually
     */
    public function update_onConfirm($recordId = null)
    {
        $model = $this->formFindModelObject($recordId);

        $model->attemptVerification();

        Flash::success(__("Subscriber has been manually confirmed!"));

        if ($redirect = $this->makeRedirect('update-close', $model)) {
            return $redirect;
        }
    }
}
