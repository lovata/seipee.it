<?php namespace Responsiv\Campaign\Classes;

use App;
use Site;
use Date;
use Responsiv\Campaign\Models\Message;
use Responsiv\Campaign\Models\Subscriber;
use Responsiv\Campaign\Models\MessageStatus;

/**
 * CampaignWorker class, engaged by the automated worker
 */
class CampaignWorker
{
    /**
     * @var Responsiv\Campaign\Classes\CampaignManager campaignManager
     */
    protected $campaignManager;

    /**
     * @var bool isReady says there should be only one task performed per execution.
     */
    protected $isReady = true;

    /**
     * @var string logMessage for processing
     */
    protected $logMessage = 'There are no outstanding activities to perform.';

    /**
     * __construct worker
     */
    public function __construct()
    {
        $this->logMessage = __("There are no outstanding activities to perform.");
        $this->campaignManager = CampaignManager::instance();
    }

    /**
     * instance creates a new instance of this singleton
     */
    public static function instance(): static
    {
        return App::make('campaign.worker');
    }

    /*
     * process all tasks
     */
    public function process()
    {
        $this->isReady && $this->processPendingMessages();
        $this->isReady && $this->processActiveMessages();

        // @todo Move this action so the user can do it manually
        // $this->isReady && $this->processUnsubscribedSubscribers();

        return $this->logMessage;
    }

    /**
     * processPendingMessages will launch pending campaigns if there launch date has
     * passed.
     */
    public function processPendingMessages()
    {
        $campaign = Site::withGlobalContext(function() {
            return (new Message)
                ->where('status_id', MessageStatus::getPendingStatus()->id)
                ->get()
                ->filter(function($message) {
                    return $message->launch_at <= Date::now();
                })
                ->shift()
            ;
        });

        if (!$campaign) {
            return;
        }

        Site::applyActiveSiteId($campaign->site_id);

        $this->campaignManager->launchCampaign($campaign);

        $this->logActivity(sprintf(
            'Launched campaign "%s" with %s subscriber(s) queued.',
            $campaign->name,
            $campaign->count_subscriber
        ));
    }

    /**
     * processActiveMessages will send messages subscribers of active campaigns.
     */
    public function processActiveMessages()
    {
        $campaign = Site::withGlobalContext(function() {
            return (new Message)
                ->where('status_id', MessageStatus::getActiveStatus()->id)
                ->get()
                ->filter(function($message) {
                    return $message->canBeProcessed();
                })
                ->shift()
            ;
        });

        if (!$campaign) {
            return;
        }

        Site::applyActiveSiteId($campaign->site_id);

        $countSent = $this->campaignManager->sendCampaign($campaign);

        $this->logActivity(sprintf(
            'Sent campaign "%s" to %s subscriber(s).',
            $campaign->name,
            $countSent
        ));
    }

    /**
     * processUnsubscribedSubscribers will find subscribers who are unsubscribed for longer
     * than 14 days and delete their account.
     */
    public function processUnsubscribedSubscribers()
    {
        $subscriber = Subscriber::whereNotNull('unsubscribed_at')
            ->get()
            ->filter(function($subscriber) {
                return $subscriber->unsubscribed_at <= Date::now()->subDays(14);
            })
            ->shift()
        ;

        if ($subscriber) {
            $subscriber->delete();

            $this->logActivity(sprintf(
                'Deleted subscriber "%s" who opted out 14 days ago.',
                $subscriber->email
            ));
        }
    }

    /**
     * logActivity is called when activity has been performed.
     */
    protected function logActivity($message)
    {
        $this->logMessage = $message;
        $this->isReady = false;
    }
}
