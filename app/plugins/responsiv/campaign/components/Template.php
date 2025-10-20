<?php namespace Responsiv\Campaign\Components;

use Event;
use Request;
use Redirect;
use Response;
use ApplicationException;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Responsiv\Campaign\Models\Message;
use Responsiv\Campaign\Models\Subscriber;
use Exception;

/**
 * Template
 */
class Template extends ComponentBase
{
    /**
     * @var string verifyPage reference to the page name for when a subscriber verifies their email.
     */
    public $verifyPage;

    /**
     * @var string unsubscribePage reference to the page name for when a subscriber opts out.
     */
    public $unsubscribePage;

    /**
     * @var bool trackingMode to display a tracking pixel
     */
    protected $trackingMode = false;

    /**
     * @var bool unsubscribeMode if user has opted-out of mailing list
     */
    protected $unsubscribeMode = false;

    /**
     * @var Responsiv\Campaign\Models\Subscriber subscriber
     */
    protected $subscriber;

    /**
     * @var Responsiv\Campaign\Models\Message campaign
     */
    protected $campaign;

    /**
     * @var Responsiv\Campaign\Models\Subscriber dynamicSubscriber
     */
    protected static $dynamicSubscriber;

    /**
     * componentDetails
     */
    public function componentDetails()
    {
        return [
            'name' => 'Campaign Template',
            'description' => 'Used for displaying web-based versions of campaign messages.'
        ];
    }

    /**
     * defineProperties
     */
    public function defineProperties()
    {
        return [
            'isDynamic' => [
                'title' => 'Dynamic Template',
                'description' => 'Template contents are custom and user-personalized for each subscriber.',
                'type' => 'checkbox',
                'default' => 0,
                'showExternalParam' => false
            ],
            'verifyPage' => [
                'title' => 'Verify Page',
                'description' => 'Page to redirect when a subscriber verifies their email.',
                'type' => 'dropdown',
                'default' => '',
                'group' => 'Links',
            ],
            'unsubscribePage' => [
                'title' => 'Unsubscribe Page',
                'description' => 'Page to redirect when a subscriber opts out.',
                'type' => 'dropdown',
                'default' => '',
                'group' => 'Links',
            ],
        ];
    }

    /**
     * getVerifyPageOptions
     */
    public function getVerifyPageOptions()
    {
        return ['' => '- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * getUnsubscribePageOptions
     */
    public function getUnsubscribePageOptions()
    {
        return ['' => '- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * onRun
     */
    public function onRun()
    {
        if (!($code = $this->param('code')) || $code == 'default') {
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }

        // Internal call
        if ($code == LARAVEL_START) {
            return;
        }

        $this->prepareVars();

        // Verify subscription
        if (get('verify')) {
            return $this->handleVerify($code);
        }

        try {
            $this->validateCampaignCode($code);
        }
        catch (Exception $ex) {
            return 'Invalid request!';
        }

        if (get('unsubscribe')) {
            return $this->handleUnsubscribe();
        }

        $this->markSubscriberAsRead();

        if ($this->trackingMode) {
            return $this->renderTrackingPixel();
        }

        return $this->campaign->renderForSubscriber($this->subscriber);
    }

    /**
     * prepareVars
     */
    protected function prepareVars()
    {
        // Page links
        $this->verifyPage = $this->page['verifyPage'] = $this->property('verifyPage');
        $this->unsubscribePage = $this->page['unsubscribePage'] = $this->property('unsubscribePage');
    }

    /**
     * setDynamicSubscriber
     */
    public static function setDynamicSubscriber($subscriber)
    {
        static::$dynamicSubscriber = $subscriber;
    }

    /**
     * getExternalSubscriber
     */
    public static function getDynamicSubscriber()
    {
        return static::$dynamicSubscriber;
    }

    /**
     * getSubscriber
     */
    public function getSubscriber()
    {
        return $this->subscriber ?: static::$dynamicSubscriber;
    }

    /**
     * markSubscriberAsRead
     */
    protected function markSubscriberAsRead()
    {
        if (!isset($this->subscriber->pivot)) {
            return;
        }

        $pivot = $this->subscriber->pivot;
        if ($pivot->read_at) {
            return;
        }

        $pivot->read_at = $this->campaign->freshTimestamp();
        $pivot->save();

        $this->campaign->count_read++;
        $this->campaign->save();
    }

    /**
     * validateCampaignCode
     */
    protected function validateCampaignCode($code)
    {
        if (ends_with($code, '.png')) {
            $this->trackingMode = true;
            $code = substr($code, 0, -4);
        }

        $parts = explode('!', base64_decode($code));
        if (count($parts) < 3) {
            throw new ApplicationException('Invalid code');
        }

        list($campaignId, $subscriberId, $hash) = $parts;

        // Render unique content for the subscriber
        $this->campaign = Message::find((int) $campaignId);
        if (!$this->campaign) {
            throw new ApplicationException('Invalid code');
        }

        $this->subscriber = $this->campaign->subscribers()
            ->where('id', (int) $subscriberId)
            ->first();

        if (!$this->subscriber) {
            $this->subscriber = Subscriber::find((int) $subscriberId);
        }

        if (!$this->subscriber) {
            throw new ApplicationException('Invalid code');
        }

        // Verify unique hash
        $verifyValue = $campaignId.'!'.$subscriberId;
        $verifyHash = md5($verifyValue.'!'.$this->subscriber->email);

        if ($hash != $verifyHash) {
            throw new ApplicationException('Invalid hash');
        }
    }

    /**
     * handleVerify
     */
    protected function handleVerify($code)
    {
        $parts = explode('!', base64_decode($code));
        if (count($parts) < 2) {
            throw new ApplicationException('Invalid code');
        }

        list($subscriberId, $hash) = $parts;

        $subscriber = Subscriber::find((int) $subscriberId);

        if (!$subscriber) {
            throw new ApplicationException('Invalid code');
        }

        $verifyCode = $subscriber->getUniqueCode();
        if ($code != $verifyCode) {
            throw new ApplicationException('Invalid hash');
        }

        $subscriber->attemptVerification(Request::ip());

        if ($pageName = $this->verifyPage) {
            return Redirect::to($this->pageUrl($pageName));
        }
        else {
            return '<html><head><title>Verification successful</title></head><body><h1>Verification successful</h1><p>Your email has been successfully added to this list!</p></body></html>';
        }
    }

    /**
     * handleUnsubscribe
     */
    protected function handleUnsubscribe()
    {
        if ($event = Event::fire('responsiv.campaign.beforeUnsubscribe', [
            $this,
            $this->subscriber,
            $this->campaign
        ], true)) {
            return $event;
        }

        if (!isset($this->subscriber->pivot)) {
            return 'You are already unsubscribed from our mailing list!';
        }

        $pivot = $this->subscriber->pivot;
        if ($pivot->stop_at) {
            return 'You are already unsubscribed from our mailing list!';
        }

        $pivot->stop_at = $this->campaign->freshTimestamp();
        $pivot->read_at = $this->campaign->freshTimestamp();
        $pivot->save();

        $this->campaign->count_read++;
        $this->campaign->count_stop++;
        $this->campaign->save();

        $this->subscriber->attemptUnsubscribe();

        if ($pageName = $this->unsubscribePage) {
            return Redirect::to($this->pageUrl($pageName));
        }
        else {
            return '<html><head><title>Unsubscribe successful</title></head><body><h1>Unsubscribe successful</h1><p>Your email has been successfully unsubscribed from this list!</p></body></html>';
        }
    }

    /**
     * renderTrackingPixel
     */
    protected function renderTrackingPixel()
    {
        header_remove();
        // Request::header('referer'); // Track referer?

        // Transparent 1x1 image/png
        $contents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');

        $response = Response::make($contents);
        $response->header('Content-Type', 'image/png');
        return $response;
    }
}
