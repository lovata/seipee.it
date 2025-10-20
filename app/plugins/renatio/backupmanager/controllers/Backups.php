<?php

namespace Renatio\BackupManager\Controllers;

use Backend\Classes\Controller;
use Backend\Facades\BackendMenu;
use Facades\Renatio\BackupManager\Models\BackupDisk;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use October\Rain\Support\Facades\Flash;
use Renatio\BackupManager\Classes\BackupsList;
use Renatio\BackupManager\Classes\BackupStatuses;
use Renatio\BackupManager\Classes\DownloadBackup;
use Renatio\BackupManager\Classes\SystemRequirements;

class Backups extends Controller
{
    public $requiredPermissions = ['renatio.backupmanager.access_backups'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Renatio.BackupManager', 'backupmanager', 'backups');
    }

    public function index()
    {
        $this->addJs('/modules/backend/widgets/lists/assets/js/october.list.js', 'core');

        $this->pageTitle = e(trans('renatio.backupmanager::lang.backups.list_title'));
        $this->bodyClass = 'slim-container';

        $this->vars['issues'] = (new SystemRequirements)();
        $this->vars['backupStatuses'] = (new BackupStatuses)();
        $this->vars['backups'] = (new BackupsList)();
    }

    public function onCreate()
    {
        $result = Artisan::call('backup:run', post('only_db') ? ['--only-db' => true] : []);

        $output = Artisan::output();

        Storage::put('backup.log', $output);

        $title = $result === 0
            ? e(trans('renatio.backupmanager::lang.notification.backup_success'))
            : e(trans('renatio.backupmanager::lang.notification.backup_failed'));

        return $this->refreshBackupPartials() + $this->makeLogPartial($output, $title);
    }

    public function onClean()
    {
        $result = Artisan::call('backup:clean');

        $output = Artisan::output();

        Storage::put('backup.log', $output);

        $title = $result === 0
            ? e(trans('renatio.backupmanager::lang.notification.cleanup_success'))
            : e(trans('renatio.backupmanager::lang.notification.cleanup_failed'));

        return $this->refreshBackupPartials() + $this->makeLogPartial($output, $title);
    }

    public function onDelete()
    {
        if (is_array(post('checked'))) {
            foreach (post('checked', []) as $path) {
                try {
                    BackupDisk::getBackup($path)->delete();
                } catch (\Throwable $e) {
                    continue;
                }
            }

            Flash::success(e(trans('backend::lang.list.delete_selected_success')));
        } else {
            Flash::error(e(trans('backend::lang.list.delete_selected_empty')));
        }

        return $this->refreshBackupPartials();
    }

    public function onSearch()
    {
        session(['backup_search' => post('backup_search')]);

        return [
            '#backupList' => $this->makePartial('backups_list',
                ['backups' => (new BackupsList)(session('backup_search'))]),
        ];
    }

    public function onDeleteBackup()
    {
        try {
            BackupDisk::getBackup(post('path'))->delete();

            Flash::success(e(trans('backend::lang.list.delete_selected_success')));
        } catch (\Throwable $e) {
            Flash::error(e(trans('backend::lang.list.delete_selected_empty')));
        }

        return $this->refreshBackupPartials();
    }

    public function onPreviewLog()
    {
        return $this->makePartial('log', [
            'output' => Storage::exists('backup.log')
                ? Storage::get('backup.log')
                : e(trans('renatio.backupmanager::lang.log.empty')),
        ]);
    }

    public function onRefresh()
    {
        return $this->refreshBackupPartials();
    }

    public function onChangeDisk()
    {
        BackupDisk::set(post('disk'));

        return $this->refreshBackupPartials();
    }

    public function download()
    {
        try {
            return (new DownloadBackup)();
        } catch (\Exception $e) {
            Flash::error($e->getMessage());

            return redirect()->back();
        }
    }

    protected function refreshBackupPartials()
    {
        return [
            '#backupStatuses' => $this->makePartial('backup_statuses_list',
                ['backupStatuses' => (new BackupStatuses)()]),
            '#backupList' => $this->makePartial('backups_list', ['backups' => (new BackupsList)()]),
        ];
    }

    protected function makeLogPartial($output, $title = null)
    {
        return ['result' => $this->makePartial('log', ['output' => $output, 'title' => $title])];
    }
}
