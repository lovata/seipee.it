<?php

namespace Renatio\SeoManager\Classes;

use October\Rain\Support\Facades\Event;
use October\Rain\Support\Facades\Yaml;
use Renatio\SeoManager\Models\Settings;

class SeoFields
{
    public function fields()
    {
        $fields = $this->seoFields();

        if ($this->isOpenGraphEnabled()) {
            $fields += $this->ogFields();
        }

        return $fields;
    }

    protected function seoFields()
    {
        $fields = Yaml::parseFile(__DIR__.'/../models/seotag/fields.yaml');

        return Event::fire('seo.extendSeoFields', [$fields], true) ?: $fields;
    }

    protected function isOpenGraphEnabled()
    {
        return Settings::get('og_enabled');
    }

    protected function ogFields()
    {
        $fields = Yaml::parseFile(__DIR__.'/../models/seotag/og_fields.yaml');

        return Event::fire('seo.extendOgFields', [$fields], true) ?: $fields;
    }

    public function translatable()
    {
        return [
            'meta_title',
            'meta_description',
            'meta_keywords',
            'canonical_url',
            'redirect_url',
            'og_title',
            'og_description',
            'og_image',
        ];
    }
}
