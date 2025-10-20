<?php

namespace Renatio\BackupManager\Classes;

use Renatio\BackupManager\Models\Settings;
use Schema;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes;
use Spatie\DbDumper\Compressors\GzipCompressor;

class BackupConfiguration
{
    public function __invoke()
    {
        if (! Schema::hasTable('system_settings')) {
            return;
        }

        $settings = Settings::instance();

        config([
            'backup.backup.name' => $settings->backup_name,

            'backup.backup.source' => [
                'files' => [
                    'include' => $settings->getIncludePaths(),
                    'exclude' => $settings->getExcludePaths(),
                    'follow_links' => $settings->follow_links,
                    'ignore_unreadable_directories' => $settings->ignore_unreadable_directories,
                ],

                'databases' => $settings->databases ?: [config('database.default')],
            ],

            'backup.backup.database_dump_compressor' => $settings->database_dump_compressor ? GzipCompressor::class : null,

            'backup.backup.destination' => [
                'filename_prefix' => $settings->filename_prefix,
                'disks' => $settings->disks ?: ['local'],
            ],

            'backup.notifications.notifications' => $this->getNotifications(),

            'backup.notifications.mail' => [
                'to' => array_map('trim', explode(',', $settings->notification_email)),

                'from' => [
                    'address' => config('mail.from.address'),
                    'name' => config('mail.from.name'),
                ],
            ],

            'backup.monitor_backups' => [
                [
                    'name' => $settings->backup_name,
                    'disks' => $settings->disks ?: ['local'],
                    'health_checks' => [
                        MaximumAgeInDays::class => $settings->monitor_max_age_in_days ?? 1,
                        MaximumStorageInMegabytes::class => $settings->monitor_max_storage_in_mb ?? 5000,
                    ],
                ],
            ],

            'backup.cleanup.default_strategy' => [
                'keep_all_backups_for_days' => $settings->keep_all ?? 7,
                'keep_daily_backups_for_days' => $settings->keep_daily ?? 16,
                'keep_weekly_backups_for_weeks' => $settings->keep_weekly ?? 8,
                'keep_monthly_backups_for_months' => $settings->keep_monthly ?? 4,
                'keep_yearly_backups_for_years' => $settings->keep_yearly ?? 2,
                'delete_oldest_backups_when_using_more_megabytes_than' => $settings->delete_oldest_when_mb ?? 5000,
            ],

            /* Add password and encryption protection */
            'backup.backup.password' => $settings->zip_password,

            'backup.backup.encryption' => ! empty($settings->zip_password) ? 'default' : null,
        ]);

        /* Exclude database tables */
        if ($settings->exclude_tables) {
            foreach (config('backup.backup.source.databases') as $database) {
                config([
                    "database.connections.{$database}.dump.exclude_tables" => $settings->exclude_tables,
                ]);
            }
        }
    }

    protected function getNotifications()
    {
        $notifications = [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class => [],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => [],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class => [],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class => [],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification::class => [],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class => [],
        ];

        $notificationSettings = is_array(Settings::get('notifications')) ? Settings::get('notifications') : [];

        foreach ($notifications as $key => $notification) {
            if (in_array(class_basename($key), $notificationSettings)) {
                $notifications[$key] = ['mail'];
            }
        }

        return $notifications;
    }
}
