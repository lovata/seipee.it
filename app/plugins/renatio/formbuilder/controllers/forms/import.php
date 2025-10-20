<?php Block::put('breadcrumb') ?>
    <ul>
        <li>
            <a href="<?= Backend::url('renatio/formbuilder/forms') ?>">
                <?= e(trans('renatio.formbuilder::lang.form.forms')) ?>
            </a>
        </li>
        <li><?= e($this->pageTitle) ?></li>
    </ul>
<?php Block::endPut() ?>

<?= Form::open(['class' => 'layout']) ?>

    <div class="layout-row">
        <?= $this->importRender() ?>
    </div>

    <div class="form-buttons">
        <button
            type="submit"
            data-control="popup"
            data-handler="onImportLoadForm"
            data-keyboard="false"
            class="btn btn-primary">
            <?= e(trans('renatio.formbuilder::lang.field.import')) ?>
        </button>

        <span class="btn-text">
            <?= e(trans('backend::lang.form.or')) ?>
            <a href="<?= Backend::url('renatio/formbuilder/forms') ?>">
                <?= e(trans('backend::lang.form.cancel')) ?>
            </a>
        </span>
    </div>

<?= Form::close() ?>
