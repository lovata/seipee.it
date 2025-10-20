<?php Block::put('breadcrumb') ?>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= Backend::url('responsiv/campaign/lists') ?>"><?= __("List") ?></a></li>
        <?php if ($formModel): ?><li class="breadcrumb-item"><a href="<?= Backend::url("responsiv/campaign/lists/preview/{$formModel->id}") ?>"><?= $formModel->name ?></a></li><?php endif ?>
        <li class="breadcrumb-item active"><?= e($this->pageTitle) ?></li>
    </ol>
<?php Block::endPut() ?>

<?= $this->formRenderDesign() ?>
