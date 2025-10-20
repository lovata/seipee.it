<div class="form-check form-switch nolink">
    <input
        class="form-check-input"
        data-request="onToggleFieldVisibility"
        data-request-data=": fieldId: <?= $record->id ?>"
        type="checkbox"
        <?php if ($record->is_visible): ?>checked<?php endif ?>
        data-stripe-load-indicator
    >
</div>
