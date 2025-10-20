<?php Block::put('breadcrumb') ?>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= Backend::url('responsiv/campaign/subscribers') ?>"><?= __("Subscriber") ?></a></li>
        <li class="breadcrumb-item active"><?= e($this->pageTitle) ?></li>
    </ol>
<?php Block::endPut() ?>

<?php if (!$this->fatalError): ?>

    <?php Block::put('form:before-form') ?>
        <?php if ($formModel->unsubscribed_at): ?>
            <?= $this->makeHintPartial(null, 'hint_unsubscribed', [
                'type' => 'danger',
            ]) ?>
        <?php elseif (!$formModel->confirmed_at): ?>
            <?= $this->makeHintPartial(null, 'hint_unconfirmed', [
                'type' => 'warning',
            ]) ?>
        <?php else: ?>
            <?= $this->makeHintPartial(null, 'hint_confirmed', [
                'type' => 'success',
            ]) ?>
        <?php endif ?>
    <?php Block::endPut() ?>

<?php endif ?>

<?= $this->formRenderDesign() ?>
