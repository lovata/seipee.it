<?php if (! empty($formModel->form_data)) : ?>
    <?php foreach ($formModel->form_data as $data) : ?>
        <p>
            <strong><?= $data['label'] ?: (! empty($data['placeholder']) ? $data['placeholder'] : $data['name']) ?></strong>: <?= $data['value'] ?>
        </p>
    <?php endforeach ?>
<?php else : ?>
    <p><?= e(trans('renatio.formbuilder::lang.logs.no_form_data')) ?></p>
<?php endif ?>
