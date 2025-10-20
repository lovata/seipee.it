<script>
    function changeDisk(disk) {
        $.request('onChangeDisk', {
            data: {page: 1, disk: disk},
            complete: function () {
                window.history.replaceState(null, null, window.location.pathname);
            }
        })
    }
</script>

<div class="toolbar-widget list-header" id="backups-toolbar">
    <div class="control-toolbar">
        <div class="toolbar-item toolbar-primary">
            <div data-control="toolbar">
                <select name="disk"
                        onchange="changeDisk(this.value)"
                        style="width: 150px;"
                        class="form-select custom-select select-no-search">
                    <?php foreach (config('backup.backup.destination.disks') as $disk) : ?>
                        <option value="<?= $disk ?>"
                            <?= $disk === Facades\Renatio\BackupManager\Models\BackupDisk::get() ? 'selected' : '' ?>><?= $disk ?>
                        </option>
                    <?php endforeach ?>
                </select>

                <button class="btn btn-danger"
                        data-request="onDelete"
                        data-attach-loading
                        data-stripe-load-indicator>
                    <i class="octo-icon-delete me-2"></i>
                    <?= e(trans('backend::lang.list.delete_selected')) ?>
                </button>
            </div>
        </div>

        <div class="toolbar-item w-150" data-calculate-width>
            <div class="loading-indicator-container size-input-text">
                <div id="<?= $this->getId() ?>"
                     class="search-input-container storm-icon-pseudo"
                     data-control="searchwidget"
                >
                    <input placeholder="<?= e(trans('backend::lang.list.search_prompt')) ?>"
                           type="text"
                           name="backup_search"
                           value="<?= session('backup_search') ?>"
                           data-request="onSearch"
                           data-request-data="page: 1"
                           data-request-complete="window.history.replaceState(null, null, window.location.pathname);"
                           data-track-input
                           data-load-indicator
                           data-load-indicator-opaque
                           class="form-control"
                           autocomplete="off"
                           data-search-input/>
                        <i class="storm-icon"></i>
                </div>
            </div>
        </div>
    </div>
</div>
