# Upgrade guide

## Upgrading To 2.0.0

Plugin requires October CMS build 420+ with Laravel 5.5 and PHP >=7.0.

Plugin settings was reset to defaults, so please review and update them for your project needs.

## Upgrading To 2.1.0

Dropbox's integration was moved to external [Dropbox Adapter](https://octobercms.com/plugin/renatio-dropboxadapter)
plugin. Read this plugin documentation how to configure Dropbox filesystem.

## Upgrading To 2.1.4

Plugin requires setting database port in a config. This only affects older installations of October CMS.

## Upgrading To 3.0.0

Main dependency `spatie/laravel-backup` was updated to 5.6.4 version.

## Upgrading To 4.0.0

Plugin requires October CMS build 467+ with Laravel 6.x and PHP >=7.2.

## Upgrading To 4.1.0

Plugin requires October CMS 1.x with Laravel 6.x and PHP >=7.3.

## Upgrading To 4.2.0

Plugin requires October CMS version 2.x with Laravel 6.x and PHP >=7.3.

Drop support for October CMS version 1.x.

## Upgrading To 5.0.0

Plugin requires October CMS version 3.0 or higher, Laravel 9.0 or higher and PHP >=8.0.

Drop support for October CMS version 2.x.

## Upgrading To 5.1.0

Mail notification settings has changed. Please go to Settings -> Backup Manager -> Notifications tab and configure them
again if you use them.
