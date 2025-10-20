<textarea
    name="<?= $field->getName() ?>"
    id="<?= $field->getId() ?>"
    autocomplete="off"
    class="form-control field-textarea size-small"
    readonly
    <?= $field->getAttributes() ?>><?= e(json_encode($field->value)) ?></textarea>
