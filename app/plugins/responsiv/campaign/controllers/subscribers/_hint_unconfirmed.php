<i class="icon-warning text-warning"></i>
<?= __("This subscriber has not confirmed their subscription and will not receive messages.") ?>

<a href="javascript:;"
    data-request="onConfirm"
    data-request-confirm="<?= __("Confirm this subscriber manually?") ?>"
    data-stripe-load-indicator
>Confirm manually</a>.
