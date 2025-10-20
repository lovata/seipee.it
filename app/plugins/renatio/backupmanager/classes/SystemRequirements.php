<?php

namespace Renatio\BackupManager\Classes;

class SystemRequirements
{
    public function __invoke()
    {
        $issues = [];

        $memoryLimit = $this->convertToBytes(ini_get('memory_limit'));
        $mb128 = 134217728;

        if ($memoryLimit !== -1 && $memoryLimit < $mb128) {
            $issues[] = e(trans(
                'renatio.backupmanager::lang.issue.memory_limit',
                ['limit' => ini_get('memory_limit')]
            ));
        }

        if (in_array('proc_open', explode(',', ini_get('disable_functions')))) {
            $issues[] = e(trans('renatio.backupmanager::lang.issue.proc_open'));
        }

        return $issues;
    }

    protected function convertToBytes($size_str)
    {
        return match (substr($size_str, -1)) {
            'M', 'm' => (int) $size_str * 1048576,
            'K', 'k' => (int) $size_str * 1024,
            'G', 'g' => (int) $size_str * 1073741824,
            default => $size_str
        };
    }
}
