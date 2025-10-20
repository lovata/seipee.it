<?php Block::put('breadcrumb') ?>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= Backend::url('responsiv/campaign/messages') ?>"><?= __("Campaign") ?></a></li>
        <?php if ($formModel): ?><li class="breadcrumb-item"><a href="<?= Backend::url('responsiv/campaign/messages/preview/'.$formModel->id) ?>"><?= __("Preview Message") ?></a></li><?php endif ?>
        <li class="breadcrumb-item active"><?= e($this->pageTitle) ?></li>
    </ol>
<?php Block::endPut() ?>

<?php if (!$this->fatalError): ?>

    <?php Block::put('form-contents') ?>

        <?php if ($formContext == 'setup'): ?>
            <div class="layout-row min-size">
                <div class="scoreboard">
                    <div data-control="toolbar">
                        <div class="scoreboard-item title-value">
                            <h4><?= __("New Campaign") ?></h4>
                            <p><?= $formModel->name ?></p>
                            <p class="description"><?= __("Template") ?>: <?= $formModel->page ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <div class="layout-row">
            <?= $this->formRender() ?>
        </div>

        <div class="form-buttons">
            <div class="loading-indicator-container">
                <?php if ($formContext === 'setup'): ?>
                    <button
                        type="button"
                        data-request="onSave"
                        data-request-data="close:1"
                        data-hotkey="ctrl+enter, cmd+enter"
                        data-load-indicator="<?= __("Saving Message...") ?>"
                        class="btn btn-secondary">
                        <?= __("Continue") ?>
                    </button>
                    <button
                        type="button"
                        class="oc-icon-trash-o btn-icon danger pull-right"
                        data-request="onDelete"
                        data-load-indicator="<?= __("Deleting Message...") ?>"
                        data-request-confirm="<?= __("Do you really want to delete this message?") ?>">
                    </button>
                    <span class="btn-text">
                        <span class="button-separator"><?= __("or") ?></span>
                        <a
                            href="<?= Backend::url('responsiv/campaign/messages') ?>"
                            class="btn btn-link p-0">
                            <?= __("Cancel") ?>
                        </a>
                    </span>
                <?php elseif ($formContext === 'send'): ?>
                    <button
                        type="submit"
                        data-request="onSend"
                        data-hotkey="ctrl+enter, cmd+enter"
                        data-request-data="close:1"
                        data-request-confirm="<?= __("This will launch the campaign and prevent any further edits. Continue?") ?>"
                        data-load-indicator="<?= __("Launching...") ?>"
                        class="btn btn-primary">
                        <?= __("Launch this Campaign!") ?>
                    </button>
                    <button
                        type="button"
                        data-request="onSave"
                        data-request-data="close:1"
                        data-hotkey="ctrl+enter, cmd+enter"
                        data-load-indicator="<?= __("Saving Message...") ?>"
                        class="btn btn-secondary">
                        <?= __("Save & Close") ?>
                    </button>
                    <span class="btn-text">
                        <span class="button-separator"><?= __("or") ?></span>
                        <a
                            href="<?= Backend::url("responsiv/campaign/messages/preview/{$formModel->id}") ?>"
                            class="btn btn-link p-0">
                            <?= __("Cancel") ?>
                        </a>
                    </span>
                <?php else: ?>
                    <button
                        type="submit"
                        data-request="onSave"
                        data-request-data="redirect:0"
                        data-hotkey="ctrl+s, cmd+s"
                        data-load-indicator="<?= __("Saving Message...") ?>"
                        class="btn btn-primary">
                        <?= __("Save") ?>
                    </button>
                    <button
                        type="button"
                        data-request="onSave"
                        data-request-data="close:1"
                        data-hotkey="ctrl+enter, cmd+enter"
                        data-load-indicator="<?= __("Saving Message...") ?>"
                        class="btn btn-secondary">
                        <?= __("Save & Preview") ?>
                    </button>
                    <button
                        type="button"
                        class="oc-icon-trash-o btn-icon danger pull-right"
                        data-request="onDelete"
                        data-load-indicator="<?= __("Deleting Message...") ?>"
                        data-request-confirm="<?= __("Do you really want to delete this message?") ?>">
                    </button>
                    <span class="btn-text">
                        <span class="button-separator"><?= __("or") ?></span>
                        <a
                            href="<?= Backend::url("responsiv/campaign/messages/preview/{$formModel->id}") ?>"
                            class="btn btn-link p-0">
                            <?= __("Cancel") ?>
                        </a>
                    </span>
                <?php endif ?>
            </div>
        </div>

    <?php Block::endPut() ?>

    <?php Block::put('form-sidebar') ?>
        <?= $this->makePartial('content_guide') ?>
    <?php Block::endPut() ?>

    <?php Block::put('body') ?>
        <?= Form::open(['class'=>'layout stretch']) ?>
            <?= $this->makeLayout('form-with-sidebar') ?>
        <?= Form::close() ?>
    <?php Block::endPut() ?>

<?php else: ?>

    <div class="padded-container">
        <p class="flash-message static error"><?= e($this->fatalError) ?></p>
        <p><a href="<?= Backend::url('responsiv/campaign/messages') ?>" class="btn btn-default"><?= __("Return to messages list") ?></a></p>
    </div>

<?php endif ?>
