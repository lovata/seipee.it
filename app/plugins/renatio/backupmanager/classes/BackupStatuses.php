<?php

namespace Renatio\BackupManager\Classes;

use Spatie\Backup\Helpers\Format;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;

class BackupStatuses
{
    public function __invoke()
    {
        return BackupDestinationStatusFactory::createForMonitorConfig(config('backup.monitor_backups'))
            ->map(fn(BackupDestinationStatus $backupDestinationStatus) => [
                'name' => $backupDestinationStatus->backupDestination()->backupName(),
                'disk' => $backupDestinationStatus->backupDestination()->diskName(),
                'reachable' => $backupDestinationStatus->backupDestination()->isReachable(),
                'healthy' => $backupDestinationStatus->isHealthy(),
                'amount' => $backupDestinationStatus->backupDestination()->backups()->count(),
                'newest' => $backupDestinationStatus->backupDestination()->newestBackup()
                    ? $backupDestinationStatus->backupDestination()->newestBackup()->date()->diffForHumans()
                    : e(trans('renatio.backupmanager::lang.backups.no_backups')),
                'usedStorage' => Format::humanReadableSize($backupDestinationStatus->backupDestination()->usedStorage()),
            ])
            ->values()
            ->toArray();
    }
}
