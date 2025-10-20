<?php namespace Responsiv\Campaign\Controllers;

use File;
use Flash;
use Redirect;
use BackendMenu;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use Backend\Classes\Controller;
use Responsiv\Campaign\Classes\CampaignManager;
use Responsiv\Campaign\Models\Message;
use Responsiv\Campaign\Models\Subscriber;
use Responsiv\Campaign\Widgets\PreviewSelector;
use ApplicationException;
use Exception;

/**
 * Messages Backend Controller
 */
class Messages extends Controller
{
    /**
     * @var array implement extensions
     */
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\RelationController::class,
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
     * @var array relationConfig for extensions.
     */
    public $relationConfig = 'config_relation.yaml';

    /**
     * @var array requiredPermissions to view this page.
     */
    public $requiredPermissions = ['responsiv.campaign.manage_messages'];

    /**
     * @var mixed previewSelectorWidget
     */
    protected $previewSelectorWidget;

    /**
     * __construct
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Responsiv.Campaign', 'campaign', 'messages');
    }

    /**
     * beforeDisplay
     */
    public function beforeDisplay()
    {
        if ($this->action == 'preview') {
            $this->previewSelectorWidget = new PreviewSelector($this);
            $this->previewSelectorWidget->bindToController();
        }
    }

    /**
     * formExtendFields adds dynamic syntax fields
     */
    public function formExtendFields($host)
    {
        if (!in_array($host->getContext(), ['update', 'setup'])) {
            return;
        }

        $fields = $host->model->getFormSyntaxFields();
        if (!is_array($fields)) {
            return;
        }

        $host->addFields($fields);
    }

    //
    // Create
    //

    /**
     * index_onCreateForm
     */
    public function index_onCreateForm()
    {
        $model = $this->formCreateModelObject();

        $options = $model->getPageOptions();

        if (!key($options)) {
            return $this->makePartial('setup_form');
        }

        $this->asExtension('FormController')->create();

        return $this->makePartial('create_form');
    }

    /**
     * index_onArchive
     */
    public function index_onArchive()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $messageId) {
                if (!$message = Message::find($messageId)) {
                    continue;
                }

                CampaignManager::instance()->archiveCampaign($message);
            }

            Flash::success(__("Successfully archived these messages!"));
        }
        else {
            Flash::error(__("There are no selected messages to archive."));
        }

        return $this->listRefresh();
    }

    /**
     * index_onCreate
     */
    public function index_onCreate()
    {
        return $this->asExtension('FormController')->create_onSave();
    }

    /**
     * index_onGenerateTemplate
     */
    public function index_onGenerateTemplate()
    {
        $templatePath = __DIR__ . '/../partials/default_page_content.html';

        try {
            $pages = Page::lists('baseFileName', 'baseFileName');
            $pageName = post('page_name');
            $pageSettings = [
                'title' => post('page_title'),
                'url' => post('page_url'),
                'description' => post('page_description'),
                'campaignTemplate' => [],
            ];

            $pageExists = array_key_exists($pageName, $pages);
            if (!$pageExists) {
                $this->createPageFromFile($templatePath, $pageName, $pageSettings, Theme::getEditTheme());
            }
        }
        catch (Exception $ex) {
            $this->handleError($ex);
        }

        return $this->index_onCreateForm();
    }

    //
    // Duplicate
    //

    /**
     * preview_onDuplicateForm
     */
    public function preview_onDuplicateForm($recordId = null)
    {
        $source = $this->formFindModelObject($recordId);
        $model = $this->formCreateModelObject();
        $model->name = $source->name;

        $this->vars['formSourceId'] = $source->id;

        $this->initForm($model, 'duplicate');
        return $this->makePartial('duplicate_form');
    }

    /**
     * preview_onDuplicate
     */
    public function preview_onDuplicate()
    {
        $source = $this->formFindModelObject(post('source_id'));

        $model = $source->duplicateCampaign();
        $model->name = post('Message[name]');
        $model->save();

        Flash::success(__("Duplicated this campaign successfully!"));

        if ($redirect = $this->makeRedirect('update-close', $model)) {
            return $redirect;
        }
    }

    //
    // Updating and Sending
    //

    /**
     * update
     */
    public function update($recordId = null, $context = null)
    {
        if ($context == 'send') {
            $this->pageTitle = __("Send campaign message!");
        }

        $this->bodyClass = 'compact-container';

        $this->vars['availableTags'] = Message::getAvailableTags();

        return $this->asExtension('FormController')->update($recordId, $context);
    }

    /**
     * onShowPreviewSelector
     */
    public function onShowPreviewSelector($recordId = null)
    {
        return $this->previewSelectorWidget->render();
    }

    /**
     * preview_onSendTestMessage
     */
    public function preview_onSendTestMessage($recordId = null)
    {
        try {
            $model = $this->formFindModelObject($recordId);

            $recipient = post('recipient_email', null);
            $subscriber = post('subscriber', null);

            if (!$recipient || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                throw new ApplicationException(__("Please specify a recipient (valid e-mail address) and a subscriber to send a test message."));
            }

            // Use the provided subscriber or signup a test subscriber
            if ($subscriber) {
                $subscriber = Subscriber::findOrFail($subscriber);
                $subscriber->email = $recipient;
            }
            else {
                $user = $this->user;

                $subscriber = Subscriber::signup([
                    'email' => $recipient,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                ]);
            }

            CampaignManager::instance()->sendToSubscriber($model, $subscriber);

            Flash::success(__("The test message has been successfully sent."));
        }
        catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
    }

    /**
     * preview_onDelete
     */
    public function preview_onDelete($recordId = null)
    {
        return $this->asExtension('FormController')->update_onDelete($recordId);
    }

    /**
     * preview_onCancel
     */
    public function preview_onCancel($recordId = null)
    {
        if ($recordId && ($model = $this->formFindModelObject($recordId))) {
            CampaignManager::instance()->cancelCampaign($model);
        }

        return Redirect::refresh();
    }

    /**
     * preview_onArchive
     */
    public function preview_onArchive($recordId = null)
    {
        if ($recordId && ($model = $this->formFindModelObject($recordId))) {
            CampaignManager::instance()->archiveCampaign($model);
        }

        return Redirect::refresh();
    }

    /**
     * preview_onRecreate
     */
    public function preview_onRecreate($recordId = null)
    {
        if ($recordId && ($model = $this->formFindModelObject($recordId))) {
            CampaignManager::instance()->recreateCampaign($model);
        }

        return Redirect::refresh();
    }

    /**
     * onSend
     */
    public function onSend($recordId = null, $context = null)
    {
        $result = $this->asExtension('FormController')->update_onSave($recordId, $context);

        if ($model = $this->formGetModel()) {
            CampaignManager::instance()->confirmReady($model);
        }

        return $result;
    }

    //
    // Helpers
    //

    /**
     * onRefreshTemplate
     */
    public function onRefreshTemplate($recordId = null)
    {
        if ($recordId && ($model = $this->formFindModelObject($recordId))) {
            $model->rebuildContent();
            $model->save();
        }

        return Redirect::refresh();
    }

    /**
     * createPageFromFile creates a page using the contents of a specified file.
     * @param string $filePath File containing page contents
     * @param string $name New Page name
     * @param string $settings Page settings
     * @param string $themeCode Theme to create the page
     * @return void
     */
    protected function createPageFromFile($filePath, $name, $settings, $themeCode)
    {
        if (!File::exists($filePath)) {
            return false;
        }

        $page = Page::inTheme($themeCode);

        $page->fill([
            'fileName' => $name,
            'markup' => File::get($filePath),
            'settings' => $settings,
        ]);

        $page->save();

        return true;
    }
}
