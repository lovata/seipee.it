<div data-control="toolbar">
    <?= Ui::button("New Subscriber", 'responsiv/campaign/subscribers/create')
        ->icon('icon-plus')
        ->primary() ?>

    <?= Ui::ajaxButton("Delete", 'onDelete')
        ->listCheckedTrigger()
        ->listCheckedRequest()
        ->icon('icon-delete')
        ->secondary()
        ->confirmMessage("Are you sure?") ?>

    <div class="toolbar-divider"></div>

    <div class="dropdown dropdown-fixed">
        <?= Ui::button("More Actions")
            ->attributes(['data-toggle' => 'dropdown'])
            ->circleIcon('icon-ellipsis-v')
            ->secondary()
        ?>
        <ul class="dropdown-menu">
            <li>
                <?= Ui::button("Import Subscribers", 'responsiv/campaign/subscribers/import')
                    ->replaceCssClass('dropdown-item')
                    ->icon('icon-upload') ?>
            </li>
            <li>
                <?= Ui::button("Export Subscribers", 'responsiv/campaign/subscribers/export')
                    ->replaceCssClass('dropdown-item')
                    ->icon('icon-download') ?>
            </li>
        </ul>
    </div>
</div>
