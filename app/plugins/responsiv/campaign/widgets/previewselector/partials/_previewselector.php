<div class="modal-header flex-row-reverse">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title"><?= __("Send Test Message") ?></h4>
</div>

<?= Form::ajax('onSendTestMessage', ['confirm' => __("Do you really want to send the test message?")]) ?>

    <div class="modal-body">

        <div class="form-group">
            <label class="form-label"><?= __("Please enter the recipient e-mail address") ?></label>
            <input type="email" name="recipient_email" value="<?= $backendUserEmail ?>" class="form-control" />
        </div>

        <div class="form-group">
            <h4><?= __("Preview subscriber (optional)") ?></h4>
            <p class="form-text"><?= __("Select a subscriber to use actual tag data for the preview.") ?></p>
        </div>

        <div class="form-group">
            <label class="form-label"><?= __("Select a subscribers list") ?></label>
            <?= $subscribersListsSelect ?>
        </div>

        <div class="form-group loading-indicator-container size-form-field">
            <div id="loading-subscribers" style="display: none" class="loading-indicator">
                <span></span>
            </div>
            <div id="subscribers-select-container"></div>
        </div>
    </div>

    <div class="modal-footer">
        <button
            type="submit"
            class="btn btn-primary"
            data-load-indicator="<?= __("Sending test message...") ?>">
            <?= __("Send Test Message") ?>
        </button>
        <span class="btn-text">
            <span class="button-separator"><?= __("or") ?></span>
            <a
                href="javascript:;"
                class="btn btn-link p-0"
                data-dismiss="popup">
                <?= __("Cancel") ?>
            </a>
        </span>
    </div>

<?= Form::close() ?>
