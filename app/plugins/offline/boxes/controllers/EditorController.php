<?php

namespace OFFLINE\Boxes\Controllers;

use Backend\Behaviors\FormController\HasMultisite;
use Backend\Classes\Controller;
use Backend\Traits\WidgetMaker;
use BackendMenu;
use October\Rain\Database\Scopes\MultisiteScope;
use OFFLINE\Boxes\Classes\Features;
use OFFLINE\Boxes\FormWidgets\BoxesEditor;
use OFFLINE\Boxes\Models\Page;
use Site;
use System\Traits\ConfigMaker;
use System\Traits\ViewMaker;

class EditorController extends Controller
{
    use WidgetMaker;
    use ConfigMaker;
    use ViewMaker;
    use HasMultisite;

    /**
     * @var bool turboVisitControl
     */
    public $turboVisitControl = 'disable';

    public $requiredPermissions = ['offline.boxes.access_editor'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('OFFLINE.Boxes', 'boxes', 'editorcontroller');
    }

    public function index()
    {
        if (Features::instance()->multisite && $redirect = $this->handleMultiSite()) {
            return $redirect;
        }

        $this->bodyClass = 'compact-container';
        $this->pageTitle = trans('offline.boxes::lang.editor');

        // Build a BoxesEditor widget and render it in full mode.
        $widget = $this->makeFormWidget(BoxesEditor::class, [
            'name' => 'boxes',
        ], $this->makeConfigFromArray([
            'mode' => 'full',
        ]));

        $widget->bindToController();

        $this->vars['widget'] = $widget;
    }
    
    protected function getModel($id): array|null|Page|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
    {
        return Page::withoutGlobalScope(MultisiteScope::class)->find($id);
    }
    
    /**
     * Initialize the site switcher logic.
     */
    private function handleMultiSite()
    {
        return $this->redirectToMultisiteModel();
    }
    
    /**
     * Copy to the Page on the target site, if available.
     */
    private function redirectToMultisiteModel(): ?\Illuminate\Http\RedirectResponse
    {
        if (!get('_site_id') || !get('site_switch') || request()?->ajax()) {
            return null;
        }

        $model = Page::withoutGlobalScope(MultisiteScope::class)->find(get('boxes_page'));

        if (!$model) {
            return null;
        }

        $site = Site::getSiteFromId(get('_site_id'));

        if (!$site) {
            return null;
        }

        $url = \Backend\Facades\Backend::url('offline/boxes/editorcontroller?boxes_page=');

        $otherSiteModel = $model->findForSite($site->id);

        if ($otherSiteModel) {
            return redirect($url . $otherSiteModel->id);
        }

        return null;
    }
}
