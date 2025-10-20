<?php

namespace Renatio\BackupManager\Models;

use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;

class BackupDisk
{
    public function set($disk)
    {
        session(['backup-disk' => $disk]);
    }

    public function get()
    {
        return session('backup-disk') ?? $this->defaultDisk();
    }

    public function getBackup($path)
    {
        $backupDestination = BackupDestination::create($this->get(), config('backup.backup.name'));

        return $backupDestination
            ->backups()
            ->first(fn(Backup $backup) => $backup->path() === $path);
    }

    protected function defaultDisk()
    {
        return array_get(config('backup.backup.destination.disks'), 0);
    }
}
