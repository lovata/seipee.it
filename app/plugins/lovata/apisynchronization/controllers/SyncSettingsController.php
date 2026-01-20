<?php namespace Lovata\ApiSynchronization\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Lovata\ApiSynchronization\Models\SyncSettings;
use Flash;

/**
 * Sync Settings Backend Controller
 */
class SyncSettingsController extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController'
    ];

    public $formConfig = '$/lovata/apisynchronization/controllers/syncsettingscontroller/config_form.yaml';

    // public $requiredPermissions = ['lovata.apisynchronization.access_settings'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Lovata.ApiSynchronization', 'apisync-menu', 'sync-settings');
    }

    /**
     * Edit sync settings (main page)
     */
    public function index()
    {
        // Get or create settings instance
        $model = SyncSettings::instance();

        $this->pageTitle = 'Sync Settings';
        $this->bodyClass = 'compact-container';

        // Call parent update method directly with the model ID
        return $this->asExtension('FormController')->update($model->id);
    }

    /**
     * Update settings
     */
    public function update($recordId = null)
    {
        // If no recordId provided, get singleton instance
        if (!$recordId) {
            $model = SyncSettings::instance();
            $recordId = $model->id;
        }

        $this->pageTitle = 'Sync Settings';
        $this->bodyClass = 'compact-container';

        return $this->asExtension('FormController')->update($recordId);
    }

    /**
     * After update callback
     */
    public function formAfterUpdate($model)
    {
        Flash::success('Sync settings have been updated successfully.');
    }
}

