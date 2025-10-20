<?php namespace Responsiv\Campaign\Models;

use Model;
use Carbon\Carbon;

/**
 * SubscriberList
 */
class SubscriberList extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'responsiv_campaign_lists';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
    ];

    /**
     * @var array Relations
     */
    public $belongsToMany = [
        'subscribers' => [
            Subscriber::class,
            'table' => 'responsiv_campaign_lists_subscribers',
            'key' => 'list_id'
        ],
    ];

    /**
     * getCountSubscribersAttribute
     */
    public function getCountSubscribersAttribute()
    {
        return $this->subscribers()->count();
    }

    /**
     * getCountSubscribersTodayAttribute
     */
    public function getCountSubscribersTodayAttribute()
    {
        $yesterday = Carbon::now()->addDays(-1)->toDateTimeString();
        return $this->subscribers()->where('created_at', '>', $yesterday)->count();
    }
}
