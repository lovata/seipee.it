<div data-control="toolbar">
    <?php if (BackendAuth::userHasAccess('renatio.formbuilder.access_field_types.create')) : ?>
        <a href="<?= Backend::url('renatio/formbuilder/fieldtypes/create') ?>"
           class="btn btn-primary oc-icon-plus">
            <?= e(trans('renatio.formbuilder::lang.field_type.new')) ?>
        </a>
    <?php endif ?>

    <?php if (BackendAuth::userHasAccess('renatio.formbuilder.access_field_types.import_export')) : ?>
        <div class="btn-group">
            <button class="btn btn-default oc-icon-download"
                    disabled="disabled"
                    data-request="onExportSelected"
                    data-list-checked-request
                    data-list-checked-trigger>
                <?= e(trans('renatio.formbuilder::lang.field.export_selected')) ?>
            </button>

            <a href="<?= Backend::url('renatio/formbuilder/fieldtypes/import') ?>"
               class="btn btn-default oc-icon-upload">
                <?= e(trans('renatio.formbuilder::lang.field.import')) ?>
            </a>
        </div>
    <?php endif ?>

    <button class="btn btn-warning oc-icon-undo"
            data-request="onRestoreAllFields"
            data-request-confirm="<?= e(trans('renatio.formbuilder::lang.field_type.restore_confirm')) ?>"
            data-stripe-load-indicator>
        <?= e(trans('renatio.formbuilder::lang.field_type.restore_all')) ?>
    </button>

    <?php if (BackendAuth::userHasAccess('renatio.formbuilder.access_field_types.delete')) : ?>
        <button class="btn btn-danger oc-icon-trash-o"
                data-request="onDelete"
                data-request-confirm="<?= e(trans('backend::lang.form.action_confirm')) ?>"
                data-list-checked-request
                data-list-checked-trigger
                data-stripe-load-indicator>
            <?= e(trans('backend::lang.list.delete_selected')) ?>
        </button>
    <?php endif ?>
</div>
