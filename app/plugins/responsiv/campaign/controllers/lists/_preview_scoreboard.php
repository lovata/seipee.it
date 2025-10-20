<div data-control="toolbar">
    <div class="scoreboard-item title-value">
        <h4><?= __("List Name") ?></h4>
        <p><?= $formModel->name ?></p>
        <p class="description"><?= __("Created") ?>: <?= $formModel->created_at->toFormattedDateString() ?></p>
    </div>
    <div class="scoreboard-item title-value">
        <h4><?= __("Subscribers") ?></h4>
        <p><?= $formModel->count_subscribers ?></p>
        <p class="description"><?= __("Today") ?>: <?= $formModel->count_subscribers_today ?></p>
    </div>
</div>
