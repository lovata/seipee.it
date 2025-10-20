<div class="list-widget list-scrollable-container" id="backup-statuses">
    <div class="control-list list-scrollable list-rowlink scroll-after scroll-active-after" data-control="listwidget">
        <div class="list-content">
            <table class="table data border-bottom-0" data-control="rowlink">
                <thead>
                <tr>
                    <th><span><?= e(trans('renatio.backupmanager::lang.field.backup_name')) ?></span></th>
                    <th><span><?= e(trans('renatio.backupmanager::lang.field.disk_name')) ?></span></th>
                    <th class="list-cell-align-center">
                        <span><?= e(trans('renatio.backupmanager::lang.field.reachable')) ?></span></th>
                    <th class="list-cell-align-center">
                        <span><?= e(trans('renatio.backupmanager::lang.field.healthy')) ?></span></th>
                    <th class="list-cell-align-center">
                        <span><?= e(trans('renatio.backupmanager::lang.field.nr_of_backups')) ?></span>
                    </th>
                    <th><span><?= e(trans('renatio.backupmanager::lang.field.newest_backup')) ?></span></th>
                    <th><span><?= e(trans('renatio.backupmanager::lang.field.used_storage')) ?></span></th>
                </tr>
                </thead>
                <tbody>
                <?php if (count($backupStatuses)): ?>
                    <?php foreach ($backupStatuses as $backupStatus) : ?>
                        <tr>
                            <td><?= $backupStatus['name'] ?></td>
                            <td><?= $backupStatus['disk'] ?></td>
                            <td class="list-cell-align-center">
                                <?= $this->makePartial('badge_column', ['value' => $backupStatus['reachable']]) ?>
                            </td>
                            <td class="list-cell-align-center">
                                <?= $this->makePartial('badge_column', ['value' => $backupStatus['healthy']]) ?>
                            </td>
                            <td class="list-cell-align-center">
                                <?= $backupStatus['amount'] ?>
                            </td>
                            <td><?= $backupStatus['newest'] ?></td>
                            <td><?= $backupStatus['usedStorage'] ?></td>
                        </tr>
                    <?php endforeach ?>
                <?php else: ?>
                    <tr class="no-data">
                        <td colspan="7" class="nolink">
                            <p class="no-data"><?= e(trans('backend::lang.list.no_records')) ?></p>
                        </td>
                    </tr>
                <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
