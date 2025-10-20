<a href="<?= Backend::url('renatio/backupmanager/backups/download?path='.urlencode($backup['path'])) ?>"
   data-tooltip-text="<?= e(trans('renatio.backupmanager::lang.help.download')) ?>"
   data-turbo="false"
   class="btn btn-primary btn-sm me-1"><i class="octo-icon-download"></i></a>

<a href="#"
   data-tooltip-text="<?= e(trans('renatio.backupmanager::lang.help.delete')) ?>"
   data-request="onDeleteBackup"
   data-request-data="path: '<?= $backup['path'] ?>'"
   data-request-confirm="<?= e(trans('renatio.backupmanager::lang.backups.delete_selected_confirm')) ?>"
   data-stripe-load-indicator
   class="btn btn-danger btn-sm"><i class="octo-icon-delete"></i></a>
