<?php

namespace Renatio\BackupManager;

use Backend\Facades\Backend;
use Illuminate\Support\Facades\Event;
use Renatio\BackupManager\Classes\BackupConfiguration;
use Renatio\BackupManager\Classes\Schedule;
use Renatio\BackupManager\Models\Settings;
use Renatio\BackupManager\Providers\BackupServiceProvider;
use Spatie\Backup\Events\BackupZipWasCreated;
use Spatie\Backup\Listeners\EncryptBackupArchive;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name' => 'renatio.backupmanager::lang.plugin.name',
            'description' => 'renatio.backupmanager::lang.plugin.description',
            'author' => 'Renatio',
            'icon' => 'octo-icon-database',
            'homepage' => 'https://octobercms.com/plugin/renatio-backupmanager',
        ];
    }

    public function boot()
    {
        $this->app->register(BackupServiceProvider::class);

        (new BackupConfiguration)();

        if (config('backup.backup.encryption')) {
            Event::listen(BackupZipWasCreated::class, EncryptBackupArchive::class);
        }
    }

    public function registerNavigation()
    {
        return [
            'backupmanager' => [
                'label' => 'renatio.backupmanager::lang.navigation.backups',
                'url' => Backend::url('renatio/backupmanager/backups'),
                'icon' => 'octo-icon-database',
                'permissions' => ['renatio.backupmanager.access_backups'],
                'order' => 500,
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'renatio.backupmanager.access_backups' => [
                'label' => 'renatio.backupmanager::lang.permissions.access_backups',
                'tab' => 'renatio.backupmanager::lang.permissions.tab',
            ],
            'renatio.backupmanager.access_settings' => [
                'label' => 'renatio.backupmanager::lang.permissions.access_settings',
                'tab' => 'renatio.backupmanager::lang.permissions.tab',
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'renatio.backupmanager::lang.settings.label',
                'description' => 'renatio.backupmanager::lang.settings.description',
                'category' => 'renatio.backupmanager::lang.settings.category',
                'icon' => 'octo-icon-database',
                'class' => Settings::class,
                'order' => 500,
                'keywords' => 'backup',
                'permissions' => ['renatio.backupmanager.access_settings'],
                'size' => 'huge',
            ],
        ];
    }

    public function registerSchedule($schedule)
    {
        (new Schedule($schedule))();
    }
}
