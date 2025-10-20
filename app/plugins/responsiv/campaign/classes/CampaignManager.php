<?php namespace Responsiv\Campaign\Classes;

use Db;
use App;
use Log;
use Mail;
use Config;
use Responsiv\Campaign\Models\Subscriber;
use Responsiv\Campaign\Models\MessageStatus;
use Responsiv\Campaign\Helpers\RecipientGroup;
use Carbon\Carbon;
use ApplicationException;
use Exception;

/**
 * CampaignManager class, used to manage campaign actions
 */
class CampaignManager
{
    /**
     * instance creates a new instance of this singleton
     */
    public static function instance(): static
    {
        return App::make('campaign.manager');
    }

    //
    // State
    //

    /**
     * confirmReady sets the status to pending, ready to be picked up by
     * the worker process.
     */
    public function confirmReady($campaign)
    {
        if (!$campaign->is_delayed) {
            $campaign->launch_at = $campaign->freshTimestamp();
        }

        $campaign->status = MessageStatus::getPendingStatus();
        $campaign->save();
    }

    /**
     * launchCampaign sets the status to active, binds all the subscribers to the message,
     * recompile the stats and repeats the campaign.
     */
    public function launchCampaign($campaign)
    {
        $campaign->status = MessageStatus::getProcessingStatus();
        $campaign->save();

        $this->bindListSubscribers($campaign);
        $this->bindGroupSubscribers($campaign);

        $campaign->rebuildStats();
        $campaign->status = MessageStatus::getActiveStatus();
        $campaign->save();

        if ($campaign->is_repeating) {
            $this->repeatCampaign($campaign);
        }
    }

    /**
     * recreateCampaign when a campaign has no subscribers
     */
    public function recreateCampaign($campaign)
    {
        if ($campaign->count_subscriber > 0) {
            throw new ApplicationException(__("Sorry, you cannot recreate this campaign because it has subscribers belonging to it."));
        }

        $campaign->status = MessageStatus::getDraftStatus();
        $campaign->save();
    }

    /**
     * archiveCampaign
     */
    public function archiveCampaign($campaign)
    {
        // Delete all subscriber info in the pivot table
        $campaign->subscribers()->detach();

        $campaign->status = MessageStatus::getArchivedStatus();
        $campaign->save();
    }

    /**
     * cancelCampaign
     */
    public function cancelCampaign($campaign)
    {
        $campaign->status = MessageStatus::getCancelledStatus();
        $campaign->save();
    }

    /**
     * repeatCampaign
     */
    public function repeatCampaign($campaign)
    {
        $duplicate = $campaign->duplicateCampaign();
        $duplicate->is_delayed = true;

        $now = $campaign->freshTimestamp();
        switch ($campaign->repeat_frequency) {
            case 'daily': $now = $now->addDay(); break;
            case 'weekly': $now = $now->addWeek(); break;
            case 'monthly': $now = $now->addMonth(); break;
            case 'yearly': $now = $now->addYear(); break;
            default: $now = $now->addYears(5); break;
        }

        $duplicate->launch_at = $now;
        $duplicate->status = MessageStatus::getPendingStatus();
        $duplicate->count_repeat++;
        $duplicate->rebuildContent();
        $duplicate->save();

        return $duplicate;
    }

    //
    // Sending
    //

    /**
     * sendCampaign handles logic for sending a single campaign message. Returns the number
     * of subscribers that were sent a message.
     */
    public function sendCampaign($campaign): int
    {
        // Immediately mark as processed to prevent multiple threads
        $campaign->markProcessed();

        $subscribers = $campaign->subscribers()->whereNull('sent_at');

        if ($campaign->is_staggered) {
            $subscribers->limit($campaign->getStaggerCount());
        }

        $subscribers = $subscribers->get();

        $countSent = 0;
        foreach ($subscribers as $subscriber) {
            if (!$subscriber->confirmed_at || $subscriber->unsubscribed_at) {
                $campaign->subscribers()->remove($subscriber);
                continue;
            }

            if ($campaign->is_dynamic_template) {
                $subscriber->pivot->content_html = $campaign->renderDynamicTemplate($subscriber);
            }

            $this->sendToSubscriber($campaign, $subscriber);

            $subscriber->pivot->sent_at = $subscriber->freshTimestamp();
            $subscriber->pivot->save();
            $campaign->count_sent++;
            $countSent++;
        }

        if (
            !$campaign->is_staggered ||
            $campaign->count_sent >= $campaign->count_subscriber
        ) {
            $campaign->status = MessageStatus::getSentStatus();
        }

        $campaign->rebuildStats();
        $campaign->save();

        return $countSent;
    }

    /**
     * sendToSubscriber
     */
    public function sendToSubscriber($campaign, $subscriber)
    {
        $html = $campaign->renderForSubscriber($subscriber);

        $text = $this->getTextMessage($campaign->getBrowserUrl($subscriber));

        try {
            Mail::rawTo($subscriber, [
                'html' => $html,
                'text' => $text
            ], function($message) use ($campaign, $subscriber) {
                $message->subject($campaign->subject);

                // Attempt to add List-Unsubscribe header
                try {
                    $fromEmail = Config::get('mail.list_unsubscribe.address', Config::get('mail.reply_to.address', Config::get('mail.from.address')));
                    $message->getHeaders()->addTextHeader(
                        'List-Unsubscribe',
                        "<mailto:{$fromEmail}?subject=Unsubscribe>, <{$campaign->getUnsubscribeUrl($subscriber)}>"
                    );
                }
                catch (Exception $e) {}
            });
        }
        catch (Exception $e) {
            Log::error(__('Sending to') . ' ' .$subscriber->email. ' ' . __('failed with') . ' '. $e->getMessage());
        }
    }

    /**
     * getTextMessage
     */
    protected function getTextMessage($browserUrl)
    {
        $lines = [];
        $lines[] = '---------------------------------------------';
        $lines[] = '------- ' . __('Graphical email content');
        $lines[] = '---------------------------------------------';
        $lines[] = __('This email contains graphical content, you may view it in your browser using the address located below');
        $lines[] = '<'.$browserUrl.'>';
        $lines[] = '---------------------------------------------';

        return implode(PHP_EOL.PHP_EOL, $lines);
    }

    //
    // Helpers
    //

    /**
     * bindListSubscribers binds all subscribers from the campaign lists to the message.
     */
    protected function bindListSubscribers($campaign)
    {
        if (!$campaign->subscriber_lists()->count()) {
            return;
        }

        foreach ($campaign->subscriber_lists as $list) {
            $ids = $list->subscribers()->whereNull('unsubscribed_at')->lists('id');

            if ($ids && count($ids) > 0) {
                $campaign->subscribers()->sync($ids, false);
            }
        }
    }

    /**
     * bindGroupSubscribers binds all subscribers from the campaign groups to the message.
     */
    public function bindGroupSubscribers($campaign)
    {
        $groups = $campaign->groups;
        if (!is_array($groups)) {
            return;
        }

        // Pair them to existing subscribers, or create them
        $ids = $this->getSubscribersFromRecipientTypes($groups);

        // Sync to the campaign
        if (count($ids) > 0) {
            $campaign->subscribers()->sync($ids, false);
        }
    }

    /**
     * getSubscribersFromRecipientTypes creates new subscribers from a recipient data groups,
     * returns an array of the new subscriber IDs.
     * @return array
     */
    public function getSubscribersFromRecipientTypes($types)
    {
        $ids = [];
        $data = [];

        if (!is_array($types)) {
            $types = [$types];
        }

        foreach ($types as $type) {
            $data += RecipientGroup::getRecipientsData($type);
        }

        // Looking for existing subscribers and pruning them from the dataset
        Db::table('responsiv_campaign_subscribers')
            ->select('id', 'email')
            ->orderBy('id')
            ->chunk(250, function($subscribers) use (&$data, &$ids) {
                foreach ($subscribers as $subscriber) {
                    if (!isset($data[$subscriber->email])) {
                        continue;
                    }

                    $ids[] = $subscriber->id;
                    unset($data[$subscriber->email]);
                }
            })
        ;

        // Creating subscribers in the remaining dataset
        foreach ($data as $email => $info) {
            try {
                $info['email'] = $email;
                $info['confirmed_at'] = Carbon::now();
                $info['created_at'] = Carbon::now();
                $info['updated_at'] = Carbon::now();
                $ids[] = Subscriber::insertGetId($info);
            }
            catch (Exception $ex) {
                traceLog(__("Unable to add group recipient") . ' ' . $email, $ex);
            }
        }

        return $ids;
    }
}
