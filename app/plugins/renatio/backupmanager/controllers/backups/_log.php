<div class="modal-header">
    <h5 class="modal-title"><?= $title ?? e(trans('renatio.backupmanager::lang.backups.preview_log')) ?></h5>

    <button type="button" class="btn-close" data-dismiss="popup"></button>
</div>

<div class="modal-body mb-4">
    <code>
        <?= nl2br(e($output)) ?>
    </code>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="popup">
        <?= e(trans('backend::lang.form.close')) ?>
    </button>
</div>
