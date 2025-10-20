<div data-control="toolbar">
    <?php if (BackendAuth::userHasAccess('renatio.formbuilder.access_forms.create')) : ?>
        <a href="<?= Backend::url('renatio/formbuilder/forms/create') ?>"
           class="btn btn-primary oc-icon-plus">
            <?= e(trans('renatio.formbuilder::lang.form.new')) ?>
        </a>
    <?php endif ?>

    <?php if (BackendAuth::userHasAccess('renatio.formbuilder.access_forms.import_export')) : ?>
        <div class="btn-group">
            <button class="btn btn-default oc-icon-download"
                    disabled="disabled"
                    data-request="onExportSelected"
                    data-list-checked-request
                    data-list-checked-trigger>
                <?= e(trans('renatio.formbuilder::lang.field.export_selected')) ?>
            </button>

            <a href="<?= Backend::url('renatio/formbuilder/forms/import') ?>"
               class="btn btn-default oc-icon-upload">
                <?= e(trans('renatio.formbuilder::lang.field.import')) ?>
            </a>
        </div>
    <?php endif ?>

    <?php if (BackendAuth::userHasAccess('renatio.formbuilder.access_forms.delete')) : ?>
        <button class="btn btn-danger oc-icon-trash-o"
                disabled="disabled"
                data-request="onDelete"
                data-request-confirm="<?= e(trans('backend::lang.form.action_confirm')) ?>"
                data-list-checked-request
                data-list-checked-trigger
                data-stripe-load-indicator>
            <?= e(trans('backend::lang.list.delete_selected')) ?>
        </button>
    <?php endif ?>
</div>
