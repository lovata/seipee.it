<div class="toolbar-widget list-header" id="backups-toolbar">
    <div class="control-toolbar">
        <div class="toolbar-item toolbar-primary">
            <div data-control="toolbar">
                <div class="btn-group">
                    <a data-control="popup"
                       data-handler="onCreate"
                       class="btn btn-primary">
                        <i class="octo-icon-hdd-o me-2"></i>
                        <?= e(trans('renatio.backupmanager::lang.backups.app')) ?>
                    </a>

                    <a class="btn btn-primary"
                       data-control="popup"
                       data-handler="onCreate"
                       data-request-data="only_db: 1">
                        <i class="octo-icon-database me-2"></i>
                        <?= e(trans('renatio.backupmanager::lang.backups.db')) ?>
                    </a>
                </div>

                <button class="btn btn-default"
                        data-request="onRefresh"
                        data-attach-loading
                        data-stripe-load-indicator>
                    <i class="octo-icon-refresh me-2"></i>
                    <?= e(trans('backend::lang.list.refresh')) ?>
                </button>

                <button class="btn btn-danger"
                        data-control="popup"
                        data-handler="onClean">
                    <i class="octo-icon-delete me-2"></i>
                    <?= e(trans('renatio.backupmanager::lang.backups.clean_old_backups')) ?>
                </button>
            </div>
        </div>

        <div class="toolbar-item" data-calculate-width>
            <a class="btn btn-info"
               data-control="popup"
               data-handler="onPreviewLog">
                <i class="octo-icon-file-text-o me-2"></i>
                <?= e(trans('renatio.backupmanager::lang.backups.preview_log')) ?>
            </a>

            <a class="btn btn-info"
               href="<?= Backend::url('system/settings/update/renatio/backupmanager/settings') ?>">
                <i class="octo-icon-cog me-2"></i>
                <?= e(trans('renatio.backupmanager::lang.backups.settings')) ?>
            </a>
        </div>
    </div>
</div>
