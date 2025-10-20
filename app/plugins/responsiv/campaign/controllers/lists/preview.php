<?php Block::put('breadcrumb') ?>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= Backend::url('responsiv/campaign/lists') ?>"><?= __("List") ?></a></li>
        <li class="breadcrumb-item active"><?= e($this->pageTitle) ?></li>
    </ol>
<?php Block::endPut() ?>

<?php if (!$this->fatalError): ?>
    <div class="scoreboard" id="<?= $this->getId('scoreboard') ?>">
        <?= $this->makePartial('preview_scoreboard') ?>
    </div>

    <div class="loading-indicator-container mb-3">
        <div class="control-toolbar form-toolbar" data-control="toolbar">
            <?= $this->makePartial('preview_toolbar') ?>
        </div>
    </div>

    <?= $this->relationRender('subscribers') ?>

<?php else: ?>

    <div class="padded-container">
        <p class="flash-message static error"><?= e($this->fatalError) ?></p>
        <p><a href="<?= Backend::url('responsiv/campaign/lists') ?>" class="btn btn-default"><?= __("Return to lists list") ?></a></p>
    </div>

<?php endif ?>
