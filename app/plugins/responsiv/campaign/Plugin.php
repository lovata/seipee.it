<?php namespace Responsiv\Campaign;

use Db;
use Event;
use Backend;
use System\Classes\PluginBase;
use Responsiv\Campaign\Classes\CampaignWorker;

/**
 * Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * pluginDetails returns information about this plugin.
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name' => "Campaign Manager",
            'description' => "Send messages to subscription lists",
            'author' => 'Responsiv',
            'icon' => 'icon-envelope-square'
        ];
    }

    /**
     * boot
     */
    public function boot()
    {
        if (class_exists(\RainLab\User\Plugin::class)) {
            $this->reverseExtendRainLabUser();
        }
    }

    /**
     * register
     */
    public function register()
    {
        $this->registerSingletons();

        $this->registerConsoleCommand('campaign.run', \Responsiv\Campaign\Console\CampaignRun::class);
    }

    /**
     * registerSingletons
     */
    protected function registerSingletons()
    {
        $this->app->singleton('campaign.worker', \Responsiv\Campaign\Classes\CampaignWorker::class);
        $this->app->singleton('campaign.manager', \Responsiv\Campaign\Classes\CampaignManager::class);
    }

    /**
     * registerComponents
     */
    public function registerComponents()
    {
        return [
            \Responsiv\Campaign\Components\Template::class => 'campaignTemplate',
            \Responsiv\Campaign\Components\Signup::class => 'campaignSignup',
        ];
    }

    /**
     * registerNavigation
     */
    public function registerNavigation()
    {
        return [
            'campaign' => [
                'label' => "Mailing List",
                'url' => Backend::url('responsiv/campaign/messages'),
                'icon' => 'icon-envelope',
                'iconSvg' => 'plugins/responsiv/campaign/assets/images/campaign-icon.svg',
                'permissions' => ['responsiv.campaign.*'],
                'order' => 500,

                'sideMenu' => [
                    'messages' => [
                        'label' => "Campaigns",
                        'icon' => 'icon-newspaper-o',
                        'url' => Backend::url('responsiv/campaign/messages'),
                        'permissions' => ['responsiv.campaign.manage_messages'],
                    ],
                    'lists' => [
                        'label' => "Lists",
                        'icon' => 'icon-list',
                        'url' => Backend::url('responsiv/campaign/lists'),
                        'permissions' => ['responsiv.campaign.manage_subscribers'],
                    ],
                    'subscribers' => [
                        'label' => "Subscribers",
                        'icon' => 'icon-user-plus',
                        'url' => Backend::url('responsiv/campaign/subscribers'),
                        'permissions' => ['responsiv.campaign.manage_subscribers'],
                    ],
                ]
            ]
        ];
    }

    /**
     * registerPermissions
     */
    public function registerPermissions()
    {
        return [
            'responsiv.campaign.manage_messages' => [
                'tab' => 'Mailing List',
                'label' => 'Manage campaigns'
            ],
            'responsiv.campaign.manage_subscribers' => [
                'tab' => 'Mailing List',
                'label' => 'Manage subscribers'
            ]
        ];
    }

    /**
     * registerMailTemplates
     */
    public function registerMailTemplates()
    {
        return [
            'responsiv.campaign::mail.confirm_subscriber',
        ];
    }

    /**
     * registerSchedule
     */
    public function registerSchedule($schedule)
    {
        $schedule->call(function(){
            CampaignWorker::instance()->process();
        })->everyFiveMinutes();
    }

    /*
     * reverseExtendRainLabUser conditional extension for the RainLab.User plugin
     */
    protected function reverseExtendRainLabUser()
    {
        Event::listen('responsiv.campaign.listRecipientGroups', function() {
            return [
                'rainlab-user-all-users' => 'All registered users',
            ];
        });

        Event::listen('responsiv.campaign.getRecipientsData', function($type) {
            if ($type != 'rainlab-user-all-users') {
                return;
            }

            $result = [];
            Db::table('users')->orderBy('id')->chunkById(100, function($users) use (&$result) {
                foreach ($users as $user) {
                    $result[$user->email] = ['first_name' => $user->name, 'last_name' => $user->surname];
                }
            });
            return $result;
        });
    }
}
