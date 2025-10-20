<?php if ($isLaunched): ?>
    <?php
        $stats = $formModel->getExtendedStats();
    ?>
    <div class="scoreboard-item control-chart" data-control="chart-pie">
        <ul>
            <?php if (!$isSent): ?>
                <li data-color="#cccccc"><?= __("Queued") ?> <span><?= $formModel->count_subscriber - $formModel->count_sent ?></span></li>
            <?php endif ?>
            <li data-color="#e5a91a"><?= __("Sent") ?> <span><?= $formModel->count_sent ?></span></li>
            <li data-color="#95b753"><?= __("Opened") ?> <span><?= $formModel->count_read ?></span></li>
            <?php if ($isSent): ?>
                <li data-color="#ff0000"><?= __("Unsubscribed") ?> <span><?= $formModel->count_stop ?></span></li>
            <?php endif ?>
        </ul>
    </div>
    <div class="scoreboard-item title-value" data-control="goal-meter" data-value="<?= $stats->open_rate ?>">
        <h4><?= __("Open rate") ?></h4>
        <p><?= $stats->open_rate ?>%</p>
        <p class="description"><?= $stats->count_unread ?> <?= __("unread") ?></p>
    </div>
    <div class="scoreboard-item title-value goal-meter-inverse" data-control="goal-meter" data-value="<?= $stats->stop_rate ?>">
        <h4><?= __("Unsubscribe rate") ?></h4>
        <p><?= $stats->stop_rate ?>%</p>
        <?php if ($isSent): ?>
            <p class="description"><?= $stats->count_happy ?> <?= __("still happy") ?></p>
        <?php else: ?>
            <p class="description"><?= $formModel->count_stop ?> <?= __("opted out") ?></p>
        <?php endif ?>
    </div>
<?php endif ?>

<?php if ($formModel->status): ?>
    <div class="scoreboard-item title-value">
        <h4><?= __("Status") ?></h4>
        <p><?= $formModel->status->name ?></p>
        <?php if ($formModel->status == 'draft'): ?>
            <p class="description"><?= __("Still making changes...") ?></p>
        <?php elseif ($formModel->status == 'processing'): ?>
            <p class="description"><?= __("Preparing subscribers...") ?></p>
        <?php elseif ($formModel->status == 'pending'): ?>
            <p class="description"><?= __("Awaiting launch...") ?></p>
        <?php elseif ($formModel->status == 'active'): ?>
            <p class="description">
                <?php if ($formModel->processed_at): ?>
                    <?= __("Last processed") ?>: <?= $formModel->processed_at->diffForHumans() ?>
                <?php else: ?>
                    <?= __("In progress..") ?>
                <?php endif ?>
            </p>
        <?php elseif ($formModel->status == 'cancelled'): ?>
            <p class="description"><?= __("Campaign cancelled") ?></p>
        <?php else: ?>
            <p class="description"><?= __("Campaign complete!") ?></p>
        <?php endif ?>
    </div>
<?php endif ?>

<div class="scoreboard-item title-value">
    <h4><?= __("Campaign") ?></h4>
    <p><?= $formModel->iterative_name ?></p>
    <p class="description"><?= __("Template") ?>: <?= $formModel->getPageName() ?></p>
</div>
