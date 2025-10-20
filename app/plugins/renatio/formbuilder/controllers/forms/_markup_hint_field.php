<p><?= trans('renatio.formbuilder::lang.field.custom_markup_hint') ?></p>

<p class="mb-0"><?= e(trans('renatio.formbuilder::lang.field.custom_markup_example')) ?>:</p>

<code>
    <pre class="fs-6">
    <?= htmlspecialchars("
<div class=\"row\">
    <div class=\"col-6\">{{ form_field('first_name') }}</div>
    <div class=\"col-6\">{{ form_field('last_name') }}</div>
</div>
    ") ?>
    </pre>
</code>
