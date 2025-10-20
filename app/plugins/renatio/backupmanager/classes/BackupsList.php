<?php

namespace Renatio\BackupManager\Classes;

use Facades\Renatio\BackupManager\Models\BackupDisk;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\Helpers\Format;

class BackupsList
{
    public function __invoke($search = null)
    {
        $disk = BackupDisk::get();

        $backupDestination = BackupDestination::create($disk, config('backup.backup.name'));

        return $backupDestination
            ->backups()
            ->map(fn(Backup $backup) => [
                'path' => $backup->path(),
                'date' => $backup->date()->format('Y-m-d H:i:s'),
                'size' => Format::humanReadableSize($backup->sizeInBytes()),
                'disk' => $disk,
            ])
            ->when($search, fn($collection) => $collection->filter(
                fn($item) => stripos($item['path'], $search) !== false)
            )
            ->paginate();
    }
}
