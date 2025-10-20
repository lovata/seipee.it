<?php

namespace Renatio\SeoManager\Classes;

use Cms\Classes\Page;
use October\Rain\Support\Facades\Event;
use RainLab\Translate\Classes\Locale;
use System\Classes\PluginManager;

class SeoCmsPage
{
    public function extend()
    {
        $this->addSeoFields();

        $this->addTranslatableSeoFields();
    }

    protected function addSeoFields()
    {
        Event::listen('cms.template.getTemplateToolbarSettingsButtons', function ($extension, $dataHolder) {
            if ($dataHolder->templateType === 'page') {
                $dataHolder->buttons[] = [
                    'button' => 'SEO',
                    'icon' => 'octo-icon-search',
                    'popupTitle' => 'renatio.seomanager::lang.settings.description',
                    'useViewBag' => true,
                    'properties' => $this->properties(),
                ];
            }
        });
    }

    protected function addTranslatableSeoFields()
    {
        if (! PluginManager::instance()->exists('RainLab.Translate')) {
            return;
        }

        if (! Locale::isAvailable()) {
            return;
        }

        Page::extend(function ($model) {
            $model->translatable = array_merge($model->translatable, [
                'seo_title', 'seo_description', 'meta_keywords', 'canonical_url', 'redirect_url', 'og_title',
                'og_description',
            ]);
        });

        Event::listen('cms.template.getTemplateToolbarSettingsButtons', function ($extension, $dataHolder) {
            if ($dataHolder->templateType === 'page') {
                $dataHolder->buttons[] = [
                    'button' => 'renatio.seomanager::lang.settings.translate',
                    'icon' => 'octo-icon-globe',
                    'popupTitle' => 'renatio.seomanager::lang.settings.translate',
                    'useViewBag' => true,
                    'properties' => $this->translatableProperties(),
                ];
            }
        });
    }

    protected function properties()
    {
        return [
            [
                'property' => 'seo_title',
                'title' => 'renatio.seomanager::lang.field.meta_title',
                'type' => 'string',
                'description' => 'renatio.seomanager::lang.comments.meta_title_short',
                'tab' => 'renatio.seomanager::lang.tab.meta',
            ],
            [
                'property' => 'seo_description',
                'title' => 'renatio.seomanager::lang.field.meta_description',
                'type' => 'text',
                'description' => 'renatio.seomanager::lang.comments.meta_description_short',
                'tab' => 'renatio.seomanager::lang.tab.meta',
            ],
            [
                'property' => 'meta_keywords',
                'title' => 'renatio.seomanager::lang.field.meta_keywords',
                'type' => 'text',
                'tab' => 'renatio.seomanager::lang.tab.meta',
            ],
            [
                'property' => 'robot_index',
                'title' => 'renatio.seomanager::lang.field.robot_index',
                'type' => 'dropdown',
                'description' => 'renatio.seomanager::lang.comments.index',
                'options' => [
                    'index' => 'renatio.seomanager::lang.robot.index',
                    'noindex' => 'renatio.seomanager::lang.robot.noindex',
                ],
                'default' => 'index',
                'tab' => 'renatio.seomanager::lang.tab.meta',
            ],
            [
                'property' => 'robot_follow',
                'title' => 'renatio.seomanager::lang.field.robot_follow',
                'type' => 'dropdown',
                'description' => 'renatio.seomanager::lang.comments.follow',
                'options' => [
                    'follow' => 'renatio.seomanager::lang.robot.follow',
                    'nofollow' => 'renatio.seomanager::lang.robot.nofollow',
                ],
                'default' => 'follow',
                'tab' => 'renatio.seomanager::lang.tab.meta',
            ],
            [
                'property' => 'robot_advanced',
                'title' => 'renatio.seomanager::lang.field.robot_advanced',
                'type' => 'text',
                'description' => 'renatio.seomanager::lang.comments.robot_advanced',
                'tab' => 'renatio.seomanager::lang.tab.meta',
            ],
            [
                'property' => 'canonical_url',
                'title' => 'renatio.seomanager::lang.field.canonical_url',
                'type' => 'text',
                'description' => 'renatio.seomanager::lang.comments.canonical_url',
                'tab' => 'renatio.seomanager::lang.tab.meta',
            ],
            [
                'property' => 'redirect_url',
                'title' => 'renatio.seomanager::lang.field.redirect_url',
                'type' => 'text',
                'description' => 'renatio.seomanager::lang.comments.redirect_url',
                'tab' => 'renatio.seomanager::lang.tab.meta',
            ],
            [
                'property' => 'og_title',
                'title' => 'renatio.seomanager::lang.field.og_title',
                'type' => 'string',
                'description' => 'renatio.seomanager::lang.comments.og_title',
                'tab' => 'renatio.seomanager::lang.tab.og_tags',
            ],
            [
                'property' => 'og_type',
                'title' => 'renatio.seomanager::lang.field.og_type',
                'type' => 'string',
                'description' => 'renatio.seomanager::lang.comments.og_type',
                'tab' => 'renatio.seomanager::lang.tab.og_tags',
            ],
            [
                'property' => 'og_description',
                'title' => 'renatio.seomanager::lang.field.og_description',
                'type' => 'text',
                'description' => 'renatio.seomanager::lang.comments.og_description',
                'tab' => 'renatio.seomanager::lang.tab.og_tags',
            ],
            [
                'property' => 'og_image',
                'title' => 'renatio.seomanager::lang.field.og_image',
                'type' => 'mediafinder',
                'description' => 'renatio.seomanager::lang.comments.og_image_comment',
                'tab' => 'renatio.seomanager::lang.tab.og_tags',
            ],
        ];
    }

    protected function translatableProperties()
    {
        $locales = Locale::listAvailable();
        $defaultLocale = Locale::getDefault()->code ?? null;

        $properties = [];

        foreach ($locales as $locale => $label) {
            if ($locale == $defaultLocale) {
                continue;
            }

            foreach ($this->properties() as $property) {
                $property['property'] = 'locale'.ucfirst($property['property']).'.'.$locale;
                $property['tab'] = $label;

                $properties[] = $property;
            }
        }

        return $properties;
    }
}
