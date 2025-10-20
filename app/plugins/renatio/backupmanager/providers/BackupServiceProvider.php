<?php

namespace Renatio\BackupManager\Providers;

use Illuminate\Notifications\NotificationServiceProvider;
use Illuminate\Support\ServiceProvider;
use Spatie\Backup\BackupServiceProvider as LaravelBackupServiceProvider;
use Spatie\CollectionMacros\CollectionMacroServiceProvider;

class BackupServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->register(LaravelBackupServiceProvider::class);

        $this->app->register(CollectionMacroServiceProvider::class);

        /*
         * Required to send email notifications
         */
        $this->app->register(NotificationServiceProvider::class);
    }
}
