<?php if (!in_array($formContext, ['send', 'setup'])): ?>

    <!-- Refresh content fields -->
    <?= Ui::callout(function() use ($formModel) { ?>
        <?= e(__('The template content is generated from the CMS page')) ?> <strong><?= $formModel->page ?></strong>,
        <?= e(__('you can')) ?>
        <a
            href="javascript:;"
            data-request="onRefreshTemplate"
            data-request-confirm="Are you sure?">
            <?= e(__('refresh the content to look for changes')) ?></a>.
    <?php }) ?>

<?php endif ?>

<?php if ($formContext == 'setup'): ?>

    <!-- Set up -->
    <?= Ui::callout(function() { ?>
        <p><?= e(__("Now it's time to write some draft content for this campaign!")) ?></p>
        <p>
            <?= e(__('Fill out the available areas with your desired message and click')) ?>
            <strong><?= e(__('Continue')) ?></strong>
            <?= e(__('below to see a preview.')) ?>
        </p>
        <p><?= e(__('You can always edit the content again before sending it.')) ?></p>
    <?php })
        ->label(e(__('Campaign Content')))
        ->icon('icon-info-circle')
    ?>

<?php elseif ($formContext == 'send'): ?>

    <!-- Sending -->
    <?= Ui::callout(function() { ?>
        <p><?= e(__('Once this campaign is sent, the content will be locked from further edits.')) ?></p>
        <p><?= e(__('Make sure all content has been spell checked')) ?></p>
    <?php })
        ->label(e(__('Launch this Campaign')))
        ->icon('icon-check-circle')
        ->success()
    ?>

    <?php if ($formModel->is_dynamic_template): ?>
        <!-- Dynamic Template -->
        <?= Ui::callout(function() { ?>
            <p><?= e(__('This is a dynamic template so that content will be unique to the subscriber.')) ?></p>
        <?php })
            ->label(e(__('Dynamic Template')))
            ->icon('icon-info-circle')
        ?>
    <?php endif ?>

<?php endif ?>

<?php if (in_array($formContext, ['update', 'setup'])): ?>

    <div class="form-preview">

        <!-- Content management -->
        <p><?= __("These variables are available") ?>:</p>
        <div class="control-balloon-selector">
            <ul>
                <?php foreach ($availableTags as $tag => $description): ?>
                    <li
                        title="<?= $description ?>"
                        data-control="dragvalue"
                        data-drag-click="true"
                        data-text-value="{<?= $tag ?>}">
                        {<?= $tag ?>}
                    </li>
                <?php endforeach ?>
            </ul>
        </div>
        <div class="small"><em><?= __("Click or drag these in to the content area") ?></em></div>

    </div>

<?php endif ?>

