<?php namespace Responsiv\Campaign\Models;

use Model;
use Event;
use Carbon\Carbon;
use ApplicationException;

/**
 * Subscriber Model
 */
class Subscriber extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'responsiv_campaign_subscribers';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array List of attribute names which are json encoded and decoded from the database.
     */
    protected $jsonable = ['meta_data'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['email', 'first_name', 'last_name'];

    /**
     * @var array Date fields
     */
    public $dates = ['confirmed_at', 'unsubscribed_at'];

    /**
     * @var array Relations
     */
    public $belongsToMany = [
        'subscriber_lists' => [
            SubscriberList::class,
            'table' => 'responsiv_campaign_lists_subscribers',
            'otherKey' => 'list_id'
        ],
        'messages' => [
            Message::class,
            'table' => 'responsiv_campaign_messages_subscribers',
            'otherKey' => 'message_id'
        ],
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'email' => 'required|between:3,64|email|unique:responsiv_campaign_subscribers',
    ];

    /**
     * @var bool Set this to true to automatically confirm the new subscriber.
     */
    public $autoConfirm;

    /**
     * afterDelete
     */
    public function afterDelete()
    {
        $this->subscriber_lists()->detach();
        $this->messages()->detach();
    }

    /**
     * beforeCreate
     */
    public function beforeCreate()
    {
        if ($this->autoConfirm) {
            $this->confirmed_at = $this->freshTimestamp();
        }
    }

    /**
     * Signs up a user as a subscriber, adds them to supplied list code.
     */
    public static function signup($details, $addToList = null, $isConfirmed = true)
    {
        if (is_string($details)) {
            $details = ['email' => $details];
        }

        if (!$email = array_get($details, 'email')) {
            throw new ApplicationException('Missing email for subscriber!');
        }

        $subscriber = self::firstOrNew(['email' => $email]);
        $subscriber->first_name = array_get($details, 'first_name');
        $subscriber->last_name = array_get($details, 'last_name');
        $subscriber->created_ip_address = array_get($details, 'created_ip_address');
        $subscriber->meta_data = array_get($details, 'meta_data', []);
        $subscriber->updated_at = $subscriber->freshTimestamp();

        if ($isConfirmed) {
            $subscriber->confirmed_ip_address = array_get($details, 'created_ip_address');
            $subscriber->confirmed_at = $subscriber->freshTimestamp();
            $subscriber->unsubscribed_at = null;
        }

        // Already subscribed and opted-in
        if ($subscriber->exists && !$subscriber->unsubscribed_at) {
            $subscriber->unsubscribed_at = null;
        }

        $subscriber->save();

        if ($addToList) {

            if (!$list = SubscriberList::where('code', $addToList)->first()) {
                throw new ApplicationException('Unable to find a list with code: ' . $addToList);
            }

            if (!$list->subscribers()->where('id', $subscriber->id)->count()) {
                $list->subscribers()->add($subscriber);
            }

        }

        return $subscriber;
    }

    /**
     * attemptVerification
     */
    public function attemptVerification($ipAddress = null)
    {
        // Already verified!
        if ($this->confirmed_at) {
            return false;
        }

        if ($ipAddress) {
            $this->confirmed_ip_address = $ipAddress;
        }

        $this->confirmed_at = $this->freshTimestamp();
        $this->unsubscribed_at = null;
        $this->forceSave();

        Event::fire('responsiv.campaign.verification', [$this]);
        return true;
    }

    /**
     * attemptUnsubscribe
     */
    public function attemptUnsubscribe()
    {
        // Already unsubscribed!
        if ($this->unsubscribed_at) {
            return false;
        }

        $this->confirmed_at = null;
        $this->unsubscribed_at = $this->freshTimestamp();
        $this->forceSave();

        Event::fire('responsiv.campaign.unsubscribe', [$this]);
        return true;
    }

    /**
     * getUniqueCode
     */
    public function getUniqueCode()
    {
        $hash = md5($this->id . '!' . $this->email);
        return base64_encode($this->id.'!'.$hash);
    }

    /**
     * checkThrottle returns true if IP address is throttled and cannot subscribe
     * again. Maximum 3 subscription every 30 minutes. Exisiting
     * objects cannot be touched more than once within 30 mins.
     * @return bool
     */
    public static function checkThrottle($email, $ip)
    {
        if (!$email || !$ip) {
            return false;
        }

        $timeLimit = Carbon::now()->subMinutes(30);

        // Check email
        $countEmail = static::make()
            ->where('email', $email)
            ->where('updated_at', '>', $timeLimit)
            ->count()
        ;

        // Check IP address
        $countIp = static::make()
            ->where('created_ip_address', $ip)
            ->where('created_at', '>', $timeLimit)
            ->count()
        ;

        return $countEmail > 0 || $countIp > 2;
    }

    /**
     * getDynamicTemplateHtml can come from the pivot table or lazy generated for preview
     */
    public function getDynamicTemplateHtml($message)
    {
        if ($this->pivot) {
            return $this->pivot->content_html;
        }

        return $message->renderDynamicTemplate($this);
    }
}
