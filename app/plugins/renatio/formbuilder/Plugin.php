<?php

namespace Renatio\FormBuilder;

use Backend\Facades\Backend;
use Bkwld\Cloner\ServiceProvider as ClonerServiceProvider;
use Illuminate\Database\Console\PruneCommand;
use Renatio\FormBuilder\Components\RenderForm;
use Renatio\FormBuilder\Components\RenderBoxForm;
use Renatio\FormBuilder\Models\FormLog;
use Renatio\FormBuilder\Models\Settings;
use Renatio\FormBuilder\Providers\ReCaptchaServiceProvider;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name' => 'renatio.formbuilder::lang.plugin.name',
            'description' => 'renatio.formbuilder::lang.plugin.description',
            'author' => 'Renatio',
            'icon' => 'octo-icon-check-square',
        ];
    }

    public function boot()
    {
        $this->app->register(ClonerServiceProvider::class);

        $this->app->register(ReCaptchaServiceProvider::class);
    }

    public function register()
    {
        $this->registerConsoleCommand('model:prune', PruneCommand::class);
    }

    public function registerNavigation()
    {
        return [
            'formbuilder' => [
                'label' => 'renatio.formbuilder::lang.navigation.formbuilder',
                'url' => Backend::url('renatio/formbuilder/forms'),
                'icon' => 'octo-icon-check-square',
                'permissions' => ['renatio.formbuilder.*'],
                'order' => 500,
                'sideMenu' => [
                    'forms' => [
                        'label' => 'renatio.formbuilder::lang.navigation.forms',
                        'icon' => 'octo-icon-check-square',
                        'url' => Backend::url('renatio/formbuilder/forms'),
                        'permissions' => ['renatio.formbuilder.access_forms'],
                    ],
                    'fieldtypes' => [
                        'label' => 'renatio.formbuilder::lang.navigation.fieldtypes',
                        'icon' => 'octo-icon-code',
                        'url' => Backend::url('renatio/formbuilder/fieldtypes'),
                        'permissions' => ['renatio.formbuilder.access_field_types'],
                    ],
                    'formlogs' => [
                        'label' => 'renatio.formbuilder::lang.navigation.formlogs',
                        'icon' => 'octo-icon-archive',
                        'url' => Backend::url('renatio/formbuilder/formlogs'),
                        'permissions' => ['renatio.formbuilder.access_form_logs'],
                    ],
                ],
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'renatio.formbuilder.access_forms' => [
                'label' => 'renatio.formbuilder::lang.permissions.access_forms',
                'tab' => 'renatio.formbuilder::lang.permissions.tab',
            ],
            'renatio.formbuilder.access_forms.create' => [
                'label' => 'renatio.formbuilder::lang.permissions.create_forms',
                'tab' => 'renatio.formbuilder::lang.permissions.tab',
            ],
            'renatio.formbuilder.access_forms.update' => [
                'label' => 'renatio.formbuilder::lang.permissions.update_forms',
                'tab' => 'renatio.formbuilder::lang.permissions.tab',
            ],
            'renatio.formbuilder.access_forms.delete' => [
                'label' => 'renatio.formbuilder::lang.permissions.delete_forms',
                'tab' => 'renatio.formbuilder::lang.permissions.tab',
            ],
            'renatio.formbuilder.access_forms.import_export' => [
                'label' => 'renatio.formbuilder::lang.permissions.import_export_forms',
                'tab' => 'renatio.formbuilder::lang.permissions.tab',
            ],
            'renatio.formbuilder.access_form_logs' => [
                'label' => 'renatio.formbuilder::lang.permissions.access_form_logs',
                'tab' => 'renatio.formbuilder::lang.permissions.tab',
            ],
            'renatio.formbuilder.access_form_logs.preview' => [
                'label' => 'renatio.formbuilder::lang.permissions.preview_form_logs',
                'tab' => 'renatio.formbuilder::lang.permissions.tab',
            ],
            'renatio.formbuilder.access_form_logs.truncate' => [
                'label' => 'renatio.formbuilder::lang.permissions.truncate_form_logs',
                'tab' => 'renatio.formbuilder::lang.permissions.tab',
            ],
            'renatio.formbuilder.access_form_logs.delete' => [
                'label' => 'renatio.formbuilder::lang.permissions.delete_form_logs',
                'tab' => 'renatio.formbuilder::lang.permissions.tab',
            ],
            'renatio.formbuilder.access_form_logs.export' => [
                'label' => 'renatio.formbuilder::lang.permissions.export_form_logs',
                'tab' => 'renatio.formbuilder::lang.permissions.tab',
            ],
            'renatio.formbuilder.access_field_types' => [
                'label' => 'renatio.formbuilder::lang.permissions.access_field_types',
                'tab' => 'renatio.formbuilder::lang.permissions.tab',
            ],
            'renatio.formbuilder.access_field_types.create' => [
                'label' => 'renatio.formbuilder::lang.permissions.create_field_types',
                'tab' => 'renatio.formbuilder::lang.permissions.tab',
            ],
            'renatio.formbuilder.access_field_types.update' => [
                'label' => 'renatio.formbuilder::lang.permissions.update_field_types',
                'tab' => 'renatio.formbuilder::lang.permissions.tab',
            ],
            'renatio.formbuilder.access_field_types.delete' => [
                'label' => 'renatio.formbuilder::lang.permissions.delete_field_types',
                'tab' => 'renatio.formbuilder::lang.permissions.tab',
            ],
            'renatio.formbuilder.access_field_types.import_export' => [
                'label' => 'renatio.formbuilder::lang.permissions.import_export_field_types',
                'tab' => 'renatio.formbuilder::lang.permissions.tab',
            ],
            'renatio.formbuilder.access_settings' => [
                'label' => 'renatio.formbuilder::lang.permissions.access_settings',
                'tab' => 'renatio.formbuilder::lang.permissions.tab',
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'renatio.formbuilder::lang.settings.label',
                'description' => 'renatio.formbuilder::lang.settings.description',
                'category' => 'renatio.formbuilder::lang.settings.category',
                'icon' => 'icon-check-square',
                'class' => Settings::class,
                'order' => 500,
                'keywords' => 'form builder contact',
                'permissions' => ['renatio.formbuilder.access_settings'],
                'size' => 'small',
            ],
        ];
    }

    public function registerComponents()
    {
        return [
            RenderForm::class => 'renderForm',
            RenderBoxForm::class => 'renderBoxForm',
        ];
    }

    public function registerPageSnippets()
    {
        return [
            RenderForm::class => 'renderForm',
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'renatio.formbuilder::mail.contact',
            'renatio.formbuilder::mail.default',
        ];
    }

    public function registerSchedule($schedule)
    {
        $schedule->command('model:prune', [
            '--model' => [FormLog::class],
        ])->daily();
    }
}
