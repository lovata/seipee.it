<?= Form::open(['id' => 'setupForm']) ?>

    <div class="modal-header flex-row-reverse">
        <button type="button" class="close" data-dismiss="popup">&times;</button>
        <h4 class="modal-title"><?= e(__('Setup Message Template')) ?></h4>
    </div>

    <div class="modal-body">

        <?php if (!$this->fatalError): ?>
            <!-- Get started -->
            <?= Ui::callout(e(__("To get you started we'll need to create a new page in your website, used as a template for sending messages to subscribers.")))
                ->label(e(__('It looks like this is the first time you are creating a campaign')))
                ->icon('icon-magic')
            ?>
        <?php else: ?>
            <p class="flash-message static error"><?= e(trans($this->fatalError)) ?></p>
        <?php endif ?>

        <div class="form-group text-field span-left">
            <label class="form-label"><?= e(__('Page name')) ?></label>
            <span class="form-control" disabled><?= e(__('Default template')) ?></span>
            <input type="hidden" name="page_title" value="<?= e(__('Default template')) ?>" />
        </div>

        <div class="form-group text-field span-right">
            <label class="form-label"><?= e(__('Page URL')) ?></label>
            <span class="form-control" disabled>/campaign/message/:code</span>
            <input type="hidden" name="page_url" value="/campaign/message/:code" />
        </div>

        <div class="form-group text-field span-full">
            <label class="form-label"><?= e(__('File name')) ?></label>
            <input type="text" name="page_name" class="form-control" value="campaign/default-template" />
            <p class="form-text"><?= e(__('You can customize this template via the CMS > Pages area later')) ?></p>
        </div>

        <input type="hidden" name="page_description" value="<?= e(__('Campaign with basic fields')) ?>" />

    </div>
    <div class="modal-footer">
        <button
            type="submit"
            data-control="popup"
            data-size="large"
            data-handler="onGenerateTemplate"
            data-dismiss="popup"
            class="btn btn-primary">
            <?= e(__('Create a Default Template')) ?>
        </button>

        <button
            type="button"
            class="btn btn-default"
            data-dismiss="popup">
            <?= e(trans('backend::lang.form.cancel')) ?>
        </button>
    </div>

    <script>
        setTimeout(
            function(){ $('#setupForm input.form-control:first').focus() },
            310
        )
    </script>

<?= Form::close() ?>
