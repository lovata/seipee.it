<style>
    .pagination {
        margin-bottom: 0 !important;
    }
</style>

<div class="list-widget list-scrollable-container" id="backup-list">
    <div class="control-list list-scrollable list-rowlink scroll-after scroll-active-after" data-control="listwidget">
        <div class="list-content">
            <table class="table data" data-control="rowlink">
                <thead>
                <tr>
                    <th class="list-checkbox">
                        <input type="checkbox" class="form-check-input"/>
                    </th>
                    <th><span><?= e(trans('renatio.backupmanager::lang.field.path')) ?></span></th>
                    <th><span><?= e(trans('renatio.backupmanager::lang.field.created_at')) ?></span></th>
                    <th><span><?= e(trans('renatio.backupmanager::lang.field.file_size')) ?></span></th>
                    <th class="w-150"><span>&nbsp;</span></th>
                </tr>
                </thead>
                <tbody>
                <?php if (count($backups)): ?>
                    <?php foreach ($backups as $backup) : ?>
                        <tr>
                            <td class="list-checkbox nolink">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="checked[]"
                                    value="<?= $backup['path'] ?>"
                                />
                            </td>
                            <td><?= $backup['path'] ?></td>
                            <td><?= $backup['date'] ?></td>
                            <td><?= $backup['size'] ?></td>
                            <td class="nolink column-button list-cell-align-right pe-4">
                                <?= $this->makePartial('actions_column', ['backup' => $backup]) ?>
                            </td>
                        </tr>
                    <?php endforeach ?>
                <?php else: ?>
                    <tr class="no-data">
                        <td colspan="4" class="nolink">
                            <p class="no-data"><?= e(trans('backend::lang.list.no_records')) ?></p>
                        </td>
                    </tr>
                <?php endif ?>
                </tbody>
            </table>

            <nav class="d-flex justify-content-end mt-3 pe-4">
                <?= $backups->links() ?>
            </nav>
        </div>
    </div>
</div>
