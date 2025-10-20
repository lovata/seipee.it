<?php
    $isCancelled = $formModel->status && $formModel->status == 'cancelled';
    $isArchived = $formModel->status && $formModel->status == 'archived';
?>
<div class="control-toolbar form-toolbar" data-control="toolbar">
    <?= Ui::button("Back", 'responsiv/campaign/messages')
        ->icon('icon-arrow-left')
        ->outline() ?>

    <div class="toolbar-divider"></div>

    <?php if (!$isLaunched): ?>
        <?= Ui::button("Launch Campaign", "responsiv/campaign/messages/update/{$formModel->id}/send")
            ->icon('icon-paper-plane')
            ->outline()
            ->success() ?>

        <div class="toolbar-divider"></div>

        <?= Ui::button("Edit Content", "responsiv/campaign/messages/update/{$formModel->id}")
            ->icon('icon-pencil')
            ->outline()
            ->primary() ?>

        <?= Ui::ajaxButton("Refresh Template", 'onRefreshTemplate')
            ->icon('icon-refresh')
            ->loadingMessage("Refreshing...")
            ->confirmMessage("Are you sure?")
            ->outline() ?>
    <?php else: ?>
        <a
            href="javascript:;"
            data-toggle="tooltip"
            data-delay="500"
            title="<?= __("Create a new campaign based on this one") ?>"
            data-control="popup"
            data-size="large"
            data-handler="onDuplicateForm"
            data-request-data="id: '<?= $formModel->id ?>'"
            class="btn btn-outline-info">
            <i class="icon-files"></i>
            <?= __("Duplicate Campaign") ?>
        </a>
    <?php endif ?>

    <div class="toolbar-divider"></div>

    <?= Ui::popupButton("Send Test Message", 'onShowPreviewSelector')
        ->icon('icon-paper-plane')
        ->outline() ?>

    <?php if ($isLaunched && !$isCancelled && !$isArchived): ?>
        <?php if ($isSent): ?>
            <?= Ui::ajaxButton("Archive Campaign", 'onArchive')
                ->icon('icon-archive')
                ->loadingMessage("Archiving Campaign...")
                ->confirmMessage("Do you really want to archive this campaign? It will remove all the subscriber statistics.")
                ->outline() ?>
        <?php else: ?>
            <?= Ui::ajaxButton("Cancel Campaign", 'onCancel')
                ->icon('icon-ban')
                ->loadingMessage("Cancelling Campaign...")
                ->confirmMessage("Do you really want to cancel this campaign?")
                ->outline() ?>
        <?php endif ?>
    <?php endif ?>

    <?= Ui::ajaxButton("Delete Campaign", 'onDelete')
        ->icon('icon-delete')
        ->loadingMessage("Deleting Campaign...")
        ->confirmMessage("Do you really want to delete this campaign?")
        ->danger()
        ->outline() ?>
</div>
