<div
    class="control-campaign-templateselector"
    data-control="campaign-templateselector"
    data-data-locker="#<?= $this->getId('input') ?>">
    <ul>
        <?php foreach ($pages as $pageName => $page): ?>
            <li data-value="<?= $pageName ?>">
                <a href="javascript:;" class="template-box">
                    <i class="template-icon icon-file-o"></i>
                    <div class="template-selected-icon">
                        <i class="icon-check"></i>
                    </div>
                </a>
                <h5 class="template-label"><?= $page->title ?></h5>
                <p class="template-description"><?= $page->description ?></p>
            </li>
        <?php endforeach ?>
    </ul>

    <!-- Data locker -->
    <input
        type="hidden"
        id="<?= $this->getId('input') ?>"
        name="<?= $name ?>"
        value="<?= $value ?>"
        class="form-control"
        autocomplete="off" />
</div>
