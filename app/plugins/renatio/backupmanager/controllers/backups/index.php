<div class="list-header">
    <?php if (! empty($issues)) : ?>
        <?= $this->makeHintPartial(null, 'issues_hint', ['type' => 'danger']) ?>
    <?php endif ?>
</div>

<?= $this->makePartial('toolbar') ?>

<div id="backupStatuses" class="list-widget-container">
    <?= $this->makePartial('backup_statuses_list') ?>
</div>

<form class="mt-5">
    <?= $this->makePartial('backups_toolbar') ?>

    <div id="backupList" class="list-widget-container mx-4">
        <?= $this->makePartial('backups_list') ?>
    </div>
</form>
