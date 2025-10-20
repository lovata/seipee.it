<?php namespace Responsiv\Campaign\Controllers;

use Flash;
use Redirect;
use BackendMenu;
use Backend\Classes\Controller;
use Responsiv\Campaign\Helpers\RecipientGroup;
use Responsiv\Campaign\Classes\CampaignManager;
use ApplicationException;
use ValidationException;

/**
 * Lists Back-end Controller
 */
class Lists extends Controller
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
    public $requiredPermissions = ['responsiv.campaign.manage_subscribers'];

    /**
     * __construct
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Responsiv.Campaign', 'campaign', 'lists');
    }

    /**
     * preview
     */
    public function preview($recordId = null, $context = null)
    {
        $result = $this->asExtension('FormController')->preview($recordId, $context);

        if ($model = $this->formGetModel()) {
            $this->pageTitle = $model->name;
        }

        return $result;
    }

    /**
     * preview_onDelete
     */
    public function preview_onDelete($recordId = null)
    {
        return $this->asExtension('FormController')->update_onDelete($recordId);
    }

    /**
     * onLoadAddRecipientGroup
     */
    public function onLoadAddRecipientGroup()
    {
        $groups = RecipientGroup::listRecipientGroups();

        $this->vars['groups'] = $groups;

        return $this->makePartial('add_recipient_group_form');
    }

    /**
     * onAddRecipientGroup
     */
    public function onAddRecipientGroup($recordId = null)
    {
        if (!$type = post('type')) {
            throw new ValidationException(['type' => __("Select a type to add to the list.!")]);
        }

        if (!$list = $this->formFindModelObject($recordId)) {
            throw new ApplicationException(__("Fatal error: unable to find list"));
        }

        $ids = CampaignManager::instance()->getSubscribersFromRecipientTypes($type);

        if (count($ids) > 0) {
            $list->subscribers()->sync($ids, false);
        }

        Flash::success(__("Added recipients to the list"));

        return Redirect::refresh();
    }
}
