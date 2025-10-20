<?php

namespace Renatio\BackupManager\Classes;

use Renatio\BackupManager\Models\Settings;

class Schedule
{
    protected $schedule;

    public function __construct($schedule)
    {
        $this->schedule = $schedule;
    }

    public function __invoke()
    {
        $settings = Settings::instance();

        $this->scheduleCommand('backup:run', $settings->db_scheduler, ['--only-db'], $settings->db_scheduler_custom);

        $this->scheduleCommand('backup:run', $settings->app_scheduler, [], $settings->app_scheduler_custom);

        $this->scheduleCommand('backup:clean', $settings->clean_scheduler, [], $settings->clean_scheduler_custom);

        $this->scheduleCommand('backup:monitor', $settings->monitor_scheduler, [], $settings->monitor_scheduler_custom);
    }

    protected function scheduleCommand($command, $when, $options = [], $cronExpression = null)
    {
        if (! $when) {
            return false;
        }

        $command = $this->schedule->command($command, $options)
            ->sendOutputTo(storage_path('app/backup.log'));

        if ($when === 'custom') {
            return $command->cron($cronExpression);
        }

        return $command->$when();
    }
}
