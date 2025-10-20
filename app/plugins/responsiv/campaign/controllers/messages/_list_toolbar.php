<div data-control="toolbar">
    <?= Ui::popupButton("New Campaign", 'onCreateForm')
        ->size(750)
        ->icon('icon-plus')
        ->primary()
    ?>
    <?= Ui::ajaxButton("Archive Selected", 'onArchive')
        ->listCheckedTrigger()
        ->listCheckedRequest()
        ->icon('icon-archive')
        ->secondary()
        ->confirmMessage("Are you sure you want to archive these campaigns?") ?>
</div>
