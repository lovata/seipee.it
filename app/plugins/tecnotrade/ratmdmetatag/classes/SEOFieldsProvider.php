<?php namespace Tecnotrade\Ratmdmetatag\Classes;

class SEOFieldsProvider
{
    /**
     * Ritorna i campi SEO per il form.
     *
     * @return array
     */
    public static function getSEOFields()
    {
        return [
            'meta_title' => [
                'label' => 'Meta Title',
                'type' => 'text',
                'span' => 'left',
                'tab' => 'SEO',
            ],
            'meta_description' => [
                'label' => 'Meta Description',
                'type' => 'textarea',
                'span' => 'full',
                'tab' => 'SEO',
            ],
            'meta_keywords' => [
                'label' => 'Meta Keywords',
                'type' => 'textarea',
                'span' => 'full',
                'tab' => 'SEO',
            ],
            'canonical_url' => [
                'label' => 'Canonical URL',
                'type' => 'text',
                'span' => 'full',
                'tab' => 'SEO',
            ],
            'meta_robots' => [
                'label' => 'Meta Robots',
                'type' => 'text',
                'span' => 'left',
                'tab' => 'SEO',
            ],
            'og_title' => [
                'label' => 'OG Title',
                'type' => 'text',
                'span' => 'left',
                'tab' => 'Open Graph',
            ],
            'og_description' => [
                'label' => 'OG Description',
                'type' => 'textarea',
                'span' => 'full',
                'tab' => 'Open Graph',
            ],
            'og_image' => [
                'label' => 'OG Image',
                'type' => 'mediafinder',
                'mode' => 'image',
                'span' => 'full',
                'tab' => 'Open Graph',
            ],
            'og_url' => [
                'label' => 'OG URL',
                'type' => 'text',
                'span' => 'full',
                'tab' => 'Open Graph',
            ],
            'og_type' => [
                'label' => 'OG Type',
                'type' => 'dropdown',
                'options' => [
                    'article' => 'Article',
                    'website' => 'Website',
                    'tag'     => 'Tag',
                    'image'   => 'Image'
                ],
                'tab' => 'Open Graph',
            ],
        ];
    }
}