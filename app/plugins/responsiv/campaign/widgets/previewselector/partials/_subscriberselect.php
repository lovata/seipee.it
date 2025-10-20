
<label class="form-label"><?= __("Please select a subscriber") ?></label>

<?= Form::select('subscriber', $subscribers, null, [
    'class' => 'form-control custom-select'
]) ?>
