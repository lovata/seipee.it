<?php

namespace Renatio\BackupManager\Models;

use Illuminate\Support\Facades\DB;
use October\Rain\Database\Model;
use October\Rain\Database\Traits\Encryptable;
use System\Behaviors\SettingsModel;

class Settings extends Model
{
    use Encryptable;

    public $implement = [SettingsModel::class];

    public $settingsCode = 'renatio_backupmanager_settings';

    public $settingsFields = 'fields.yaml';

    protected $encryptable = ['zip_password'];

    public function initSettingsData()
    {
        foreach ($this->getDefaultSettings() as $key => $setting) {
            $this->{$key} = $setting;
        }
    }

    public function getDefaultSettings()
    {
        return [
            'databases' => [
                config('database.default'),
            ],
            'exclude_tables' => ['backend_access_log', 'system_event_logs', 'system_request_logs'],
            'database_dump_compressor' => null,
            'follow_links' => false,
            'include' => [],
            'exclude' => [
                ['path' => 'vendor'],
                ['path' => 'node_modules'],
            ],
            'ignore_unreadable_directories' => false,
            'backup_name' => config('app.name', 'backups'),
            'filename_prefix' => null,
            'disks' => ['local'],
            'db_scheduler' => null,
            'app_scheduler' => null,
            'clean_scheduler' => null,
            'monitor_scheduler' => null,
            'zip_password' => null,
            'zip_password_encryption' => null,
            'keep_all' => 7,
            'keep_daily' => 16,
            'keep_weekly' => 8,
            'keep_monthly' => 4,
            'keep_yearly' => 2,
            'delete_oldest_when_mb' => 5000,
            'monitor_max_age_in_days' => 1,
            'monitor_max_storage_in_mb' => 5000,
            'notifications' => null,
            'notification_email' => null,
        ];
    }

    public function getIncludePaths()
    {
        if (empty($this->include)) {
            return [base_path()];
        }

        return collect($this->include)
            ->flatten()
            ->map(fn($path) => base_path($path))
            ->toArray();
    }

    public function getExcludePaths()
    {
        if (empty($this->exclude)) {
            return [
                base_path('vendor'),
                base_path('node_modules'),
            ];
        }

        return collect($this->exclude)
            ->flatten()
            ->map(fn($path) => base_path($path))
            ->toArray();
    }

    public function getDatabasesOptions()
    {
        $keys = array_keys(config('database.connections'));

        return array_combine($keys, $keys);
    }

    public function getDisksOptions()
    {
        $keys = array_keys(config('filesystems.disks'));

        return array_combine($keys, $keys);
    }

    public function getSchedulerOptions()
    {
        return [
            'everyFiveMinutes' => e(trans('renatio.backupmanager::lang.schedule.every_five_minutes')),
            'everyTenMinutes' => e(trans('renatio.backupmanager::lang.schedule.every_ten_minutes')),
            'everyFifteenMinutes' => e(trans('renatio.backupmanager::lang.schedule.every_fifteen_minutes')),
            'everyThirtyMinutes' => e(trans('renatio.backupmanager::lang.schedule.every_thirty_minutes')),
            'hourly' => e(trans('renatio.backupmanager::lang.schedule.hourly')),
            'daily' => e(trans('renatio.backupmanager::lang.schedule.daily')),
            'weekly' => e(trans('renatio.backupmanager::lang.schedule.weekly')),
            'monthly' => e(trans('renatio.backupmanager::lang.schedule.monthly')),
            'custom' => e(trans('renatio.backupmanager::lang.schedule.custom')),
        ];
    }

    public function getExcludeTablesOptions()
    {
        return DB::connection()->getDoctrineSchemaManager()->listTableNames();
    }
}
