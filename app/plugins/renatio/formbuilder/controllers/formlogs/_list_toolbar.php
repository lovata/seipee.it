<div data-control="toolbar">
    <?php if (BackendAuth::userHasAccess('renatio.formbuilder.access_form_logs.export')) : ?>
        <a href="<?= Backend::url('renatio/formbuilder/formlogs/export') ?>"
           class="btn btn-default oc-icon-download">
            <?= e(trans('renatio.formbuilder::lang.field.export')) ?>
        </a>
    <?php endif ?>

    <?php if (BackendAuth::userHasAccess('renatio.formbuilder.access_form_logs.truncate')) : ?>
        <a href="javascript:;"
           data-request="onEmptyLog"
           data-request-confirm="<?= e(trans('backend::lang.form.action_confirm')) ?>"
           data-load-indicator="<?= e(trans('renatio.formbuilder::lang.logs.empty_loading')) ?>"
           class="btn btn-warning oc-icon-eraser">
            <?= e(trans('renatio.formbuilder::lang.logs.empty_link')) ?>
        </a>
    <?php endif ?>

    <?php if (BackendAuth::userHasAccess('renatio.formbuilder.access_form_logs.delete')) : ?>
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
