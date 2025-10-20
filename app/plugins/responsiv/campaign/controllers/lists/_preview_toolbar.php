<?= Ui::button("Back", 'responsiv/campaign/lists')->icon('icon-arrow-left')->outline() ?>

<div class="toolbar-divider"></div>

<?= Ui::button("Edit List Details", 'responsiv/campaign/lists/update/'.$formModel->id)->icon('icon-pencil')->outline()->primary() ?>

<?= Ui::popupButton("Add Recipient Group", 'onLoadAddRecipientGroup')->icon('icon-users')->outline() ?>

<?php if (false /*@todo*/ && class_exists('RainLab\User\Plugin')): ?>
    <?= Ui::popupButton("Sync with Users", 'onAjax')->icon('icon-refresh')->outline() ?>
<?php endif ?>

<div class="toolbar-divider"></div>

<?= Ui::ajaxButton("Delete This List", 'onDelete')->icon('icon-delete')->outline()->danger()
    ->loadingMessage("Deleting List...")
    ->confirmMessage("Do you really want to delete this list?") ?>
