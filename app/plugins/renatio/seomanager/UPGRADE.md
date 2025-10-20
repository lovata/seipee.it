# Upgrade guide

## Upgrading To 1.1.0

Plugin requires October CMS build 420+ with Laravel 5.5 and PHP >=7.0.

## Upgrading To 1.2.0

Parameters for `seo.beforeComponentRender` event were changed. Now the first parameter is `$component` itself. You can
access it `seoTag` property as in example in **README**. The second parameter is the `$page` variable.

## Upgrading To 2.0.1

Plugin requires October CMS version 2.x with Laravel 6 and PHP >=7.2.9.

Drop support for October CMS version 1.x.

## Upgrading To 3.0.0

### Remove seo_tag relation for models implementing SEO model behavior

October CMS and RainLab.Translate does not fully support polymorphic relation for models.

To solve this problem and make integration with other models available you must add columns to model database table.
There is new console command that will scan for all models that implement SeoModel behavior and migrate database tables
to add required columns.

```
php artisan seo:migrate-tables
```

There is also new command that will migrate old data from `renatio_seomanager_seo_tags` to models that implement
SeoModel behavior.

```
php artisan seo:patch 3.0
```

### Access SEO Tag before rendered on page

Access SEO Tag before rendered on page changed. Now you just need to return the model, that will have SEO fields.
The `seo_tag` relation on model is no longer supported. Read more in documentation.

```
/*
 * Before
 */
Event::listen('seo.beforeComponentRender', function ($component, $page) {
    if ($page->url == '/products/:slug') {
        $component->seoTag = $page->controller->vars['product']->seo_tag;
    }
});
```

```
/*
 * Now
 */
Event::listen('seo.beforeComponentRender', function ($component, $page) {
    if ($page->url == '/products/:slug') {
        $component->seoTag = $page->controller->vars['product'];
    }
});
```

## Upgrading To 4.0.0

Plugin requires October CMS version 3.0 or higher, Laravel 9.0 or higher and PHP >=8.0.

Add October CMS Tailor support.

Drop support for October CMS version 2.x.

## Upgrading To 5.0.0

Plugin requires October CMS version 3.1 or higher.

Add October CMS Multisite support for RainLab.Translate plugin v2.0
