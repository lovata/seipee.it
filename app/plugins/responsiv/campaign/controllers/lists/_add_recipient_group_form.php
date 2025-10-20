<?= Form::open(['id' => 'addGroupForm']) ?>
    <div class="modal-header flex-row-reverse">
        <button type="button" class="close" data-dismiss="popup">&times;</button>
        <h4 class="modal-title"><?= __("Add Recipient Group") ?></h4>
    </div>
    <div class="modal-body">

        <?php if ($this->fatalError): ?>
            <p class="flash-message static error"><?= $fatalError ?></p>
        <?php endif ?>

        <p><?= __("Fill this list with the recipients from group") ?>:</p>

        <div class="form-preview">
            <?php foreach ($groups as $code => $group): ?>
                <div class="form-group">
                    <div class="radio custom-radio">
                        <input
                            type="radio"
                            name="type"
                            value="<?= e($code) ?>"
                            id="group<?= e($code) ?>">
                        <label class="storm-icon-pseudo" for="group<?= e($code) ?>">
                            <?= e($group) ?>
                        </label>
                    </div>
                </div>
            <?php endforeach ?>
        </div>

    </div>

    <div class="modal-footer">
        <button
            type="submit"
            class="btn btn-primary"
            data-request="onAddRecipientGroup"
            data-popup-load-indicator>
            <?= e(trans('backend::lang.form.add')) ?>
        </button>
        <button
            type="button"
            class="btn btn-default"
            data-dismiss="popup">
            <?= e(trans('backend::lang.form.cancel')) ?>
        </button>
    </div>
<?= Form::close() ?>
