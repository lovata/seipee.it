<?php namespace Responsiv\Campaign\Components;

use Cms;
use Mail;
use Request;
use Validator;
use ValidationException;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Responsiv\Campaign\Models\Subscriber;
use Responsiv\Campaign\Models\SubscriberList;
use Exception;

/**
 * Signup
 */
class Signup extends ComponentBase
{

    /**
     * componentDetails
     */
    public function componentDetails()
    {
        return [
            'name' => 'Signup Form',
            'description' => 'Sign up a new person to a campaign mailing list.'
        ];
    }

    /**
     * defineProperties
     */
    public function defineProperties()
    {
        return [
            'list' => [
                'title' => 'Add to list',
                'description' => 'The campaign list code to subscribe the person to.',
                'type' => 'dropdown'
            ],
            'confirm' => [
                'title' => 'Require confirmation',
                'description' => 'Subscribers must confirm their email address.',
                'type' => 'checkbox',
                'default' => 0,
                'showExternalParam' => false
            ],
            'metaData' => [
                'title' => 'Allow meta data',
                'description' => 'Subscription form supports capturing extra meta data.',
                'type' => 'checkbox',
                'default' => 0,
                'showExternalParam' => false
            ],
            'templatePage' => [
                'title' => 'Confirmation page',
                'description' => 'If confirmation is required, select any mail template used for generating a confirmation URL link.',
                'type' => 'dropdown',
                'showExternalParam' => false
            ],
        ];
    }

    /**
     * getTemplatePageOptions
     */
    public function getTemplatePageOptions()
    {
        return Page::withComponent('campaignTemplate')->sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * getListOptions
     */
    public function getListOptions()
    {
        return SubscriberList::orderBy('name')->lists('name', 'code');
    }

    /**
     * onSignup
     */
    public function onSignup()
    {
        // Validate input
        $data = [
            'email' => mb_strtolower((string) post('email')),
            'first_name' => (string) post('first_name'),
            'last_name' => (string) post('last_name')
        ];

        if ($this->property('metaData', false)) {
            $data['meta_data'] = post('meta', []);
        }

        $rules = [
            'email' => 'required|email|min:2|max:64',
        ];

        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        try {
            // Create and add the subscriber
            $isThrottled = $this->checkThrottle($data['email']);

            if (!$isThrottled) {
                $subscriber = $this->listSubscribe($data);
                $requireConfirmation = !$subscriber->confirmed_at;
            }
            else {
                $requireConfirmation = null;
            }

            $this->page['error'] = null;
            $this->page['isThrottled'] = $isThrottled;
            $this->page['requireConfirmation'] = $requireConfirmation;

        }
        catch (Exception $ex) {
            $this->page['error'] = $ex->getMessage();
        }
    }

    /**
     * listSubscribe
     */
    protected function listSubscribe(array $data)
    {
        $listCode = $this->property('list');
        $requireConfirmation = $this->property('confirm', false);

        $subscriber = Subscriber::signup([
            'email' => $data['email'] ?? null,
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'meta_data' => $data['meta_data'] ?? [],
            'created_ip_address' => Request::ip()
        ], $listCode, !$requireConfirmation);

        // Send confirmation email
        if (!$subscriber->confirmed_at) {
            $params = [
                'confirmUrl' => $this->getConfirmationUrl($subscriber)
            ];

            Mail::sendTo($subscriber->email, 'responsiv.campaign::mail.confirm_subscriber', $params);
        }

        return $subscriber;
    }

    /**
     * getConfirmationUrl
     */
    protected function getConfirmationUrl($subscriber)
    {
        $pageName = $this->property('templatePage');

        return Cms::pageUrl($pageName, ['code' => $subscriber->getUniqueCode()]) . '?verify=1';
    }

    /**
     * checkThrottle returns true if user is throttled.
     */
    protected function checkThrottle($email)
    {
        return Subscriber::checkThrottle($email, Request::ip());
    }
}