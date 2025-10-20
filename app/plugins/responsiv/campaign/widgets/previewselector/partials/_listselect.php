<?= Form::select('subscribers_list', $subscribersListsOptions, null, [
    'data-request-loading' => '#loading-subscribers',
    'data-request-complete' => "$('#subscribers-select-container').find('select').select()",
    'data-request' => $alias . '::onSubscribersListSelected',
    'class' => 'form-control custom-select',
]) ?>
