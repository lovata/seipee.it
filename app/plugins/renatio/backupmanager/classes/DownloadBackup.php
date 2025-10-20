<?php

namespace Renatio\BackupManager\Classes;

use Facades\Renatio\BackupManager\Models\BackupDisk;
use Spatie\Backup\BackupDestination\Backup;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class DownloadBackup
{
    public function __invoke()
    {
        $backup = BackupDisk::getBackup(get('path'));

        if (! $backup) {
            throw new UnprocessableEntityHttpException('Backup not found!');
        }

        return $this->respondWithBackupStream($backup);
    }

    public function respondWithBackupStream(Backup $backup): StreamedResponse
    {
        $fileName = pathinfo($backup->path(), PATHINFO_BASENAME);

        $downloadHeaders = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Type' => 'application/zip',
            'Content-Length' => $backup->sizeInBytes(),
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Pragma' => 'public',
        ];

        return response()->stream(function () use ($backup) {
            $stream = $backup->stream();

            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, $downloadHeaders);
    }
}
