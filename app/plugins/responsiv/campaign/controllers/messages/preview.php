<?php Block::put('breadcrumb') ?>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= Backend::url('responsiv/campaign/messages') ?>"><?= __("Campaign") ?></a></li>
        <li class="breadcrumb-item active"><?= e($this->pageTitle) ?></li>
    </ol>
<?php Block::endPut() ?>

<?php if (!$this->fatalError): ?>

    <?php
        $isLaunched = $formModel->status && $formModel->status != 'draft';
        $isPending = $formModel->status && in_array($formModel->status, ['pending', 'processing']);
        $isSent = $formModel->status && $formModel->status == 'sent';
        $isEmpty = $isLaunched && !$isPending && !$formModel->count_subscriber;
    ?>

    <div class="scoreboard">
        <div data-control="toolbar">
            <?= $this->makePartial('preview_scoreboard', [
                'isLaunched' => $isLaunched,
                'isSent' => $isSent
            ]) ?>
        </div>
    </div>

    <?php if ($isEmpty): ?>
        <!-- Empty campaign -->
        <?= Ui::callout(function() { ?>
            <p>
                <?= e(__("Oops! It looks like your campaign hasn't got any subscribers...")) ?>
                <?= e(__('You can restore this campaign and try launching it again.')) ?>
            </p>
            <p>
                <button
                    data-request="onRecreate"
                    data-load-indicator="Recreating..."
                    class="btn btn-danger">
                    <?= e(__('Try again')) ?>
                </button>
            </p>
        <?php })
            ->label(e(__('Campaign has no subscribers')))
            ->icon('icon-user-times')
            ->cssClass('padded-container loading-indicator-container indicator-inset')
            ->danger()
        ?>
    <?php endif ?>

    <div class="loading-indicator-container mb-3">
        <?= $this->makePartial('preview_toolbar', [
            'isLaunched' => $isLaunched,
            'isSent' => $isSent
        ]) ?>
    </div>

    <div class="control-tabs content-tabs tabs-inset" data-control="tab">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#messageTemplate"><?= __("Preview") ?></a></li>
            <?php if ($isLaunched): ?>
                <li><a href="#messageDetails"><?= __("Details") ?></a></li>
                <?php if ($formModel->status != 'archived'): ?>
                    <li><a href="#messageSubscribers"><?= __("Recipients") ?></a></li>
                <?php endif ?>
            <?php endif ?>
        </ul>
        <div class="tab-content">
            <div class="tab-pane active">
                <div class="pt-3">
                    <?= Ui::callout(function() use ($formModel) { ?>
                        <?= e(__('Subject')) ?> - <strong><?= $formModel->subject ?></strong>
                    <?php }) ?>
                </div>
                <?= $this->makePartial('message_iframe') ?>
            </div>
            <?php if ($isLaunched): ?>
                <div class="tab-pane">
                    <form class="pt-4">
                        <?= $this->formRenderPreview() ?>
                    </form>
                </div>
                <?php if ($formModel->status != 'archived'): ?>
                    <div class="tab-pane pt-4">
                        <?= $this->relationRender('subscribers') ?>
                    </div>
                <?php endif ?>
            <?php endif ?>
        </div>
    </div>

<?php else: ?>

    <p class="flash-message static error"><?= e($this->fatalError) ?></p>
    <p><a href="<?= Backend::url('responsiv/campaign/messages') ?>" class="btn btn-default"><?= __("Return to messages list") ?></a></p>

<?php endif ?>
