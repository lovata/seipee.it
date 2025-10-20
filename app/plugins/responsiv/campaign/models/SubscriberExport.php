<?php namespace Responsiv\Campaign\Models;

use Backend\Models\ExportModel;

/**
 * SubscriberExport Model
 */
class SubscriberExport extends ExportModel
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'responsiv_campaign_subscribers';

    /**
     * @var array Relations
     */
    public $belongsToMany = [
        'subscriber_lists' => [
            SubscriberList::class,
            'table' => 'responsiv_campaign_lists_subscribers',
            'key' => 'subscriber_id',
            'otherKey' => 'list_id'
        ],
    ];

    /**
     * The accessors to append to the model's array form.
     * @var array
     */
    protected $appends = [
        'lists'
    ];

    /**
     * exportData
     */
    public function exportData($columns, $sessionKey = null)
    {
        $result = self::make()
            ->with([
                'subscriber_lists'
            ])
            ->get()
            ->toArray()
        ;

        return $result;
    }

    /**
     * getListsAttribute
     */
    public function getListsAttribute()
    {
        if (!$this->subscriber_lists) {
            return '';
        }

        return $this->encodeArrayValue($this->subscriber_lists->lists('name'));
    }
}
