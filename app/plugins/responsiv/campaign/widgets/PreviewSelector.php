<?php namespace Responsiv\Campaign\Widgets;

use Backend\Classes\WidgetBase;
use Backend\Facades\BackendAuth;
use Responsiv\Campaign\Models\SubscriberList;

/**
 * PreviewSelector Form Widget
 */
class PreviewSelector extends WidgetBase
{
    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'campaign_previewselector';

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('previewselector');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $this->vars['backendUserEmail'] = BackendAuth::user()->email;

        $subscriberLists = SubscriberList::all()->pluck('name', 'code')->toArray();
        $subscribersListsOptions = ['' => __("-- Select a list --")] + $subscriberLists;

        $this->vars['subscribersListsSelect'] = $this->makePartial('listselect', [
            'subscribersListsOptions' => $subscribersListsOptions,
            'alias' => $this->alias
        ]);
    }

    /**
     * Returns a dropdown with subscribers after the list has been selected in the widget
     *
     * @return array|null
     */
    public function onSubscribersListSelected()
    {
        $code = post('subscribers_list');

        if (empty($code)) {
            return null;
        }

        $list = SubscriberList::where('code', $code)->firstOrFail();
        $sampleSubscribers = $list->subscribers()->limit(500)->pluck('email', 'id');

        return [
            '#subscribers-select-container' => $this->makePartial('subscriberselect', [
                'subscribers' => $sampleSubscribers
            ]),
        ];
    }
}
