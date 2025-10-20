<?php namespace Tecnotrade\Ratmdmetatag\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Cms\Classes\Controller;
use RatMd\BlogHub\Models\Tag;

class RatMdMetaTag extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'SEO Manager',
            'description' => 'Displays SEO head meta for CMS pages and RatMd BlogHub Tags.',
        ];
    }

    public function defineProperties()
    {
        return [
            'ratMdTagPage' => [
                'title'       => 'Tag Page',
                'description' => 'Category page of RatMd BlogHub',
                'type'        => 'dropdown',
            ],
        ];
    }

    public function onRender()
    {
        $tagPage = $this->property('ratMdTagPage') . '.htm';
        $this->page['tagPage'] = $tagPage;
        $seoData = $this->getSeoData();
        $this->page['headTags'] = $this->generateHeadTags($seoData);
    }

    public function getRatMdTagPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    protected function getSeoData()
    {
        $model = null;

        if ($this->param('slug')) {
            // Cerca il modello Tag
            $model = Tag::where('slug', $this->param('slug'))->first();
        }

        if (!$model) {
            return [];
        }

        // Restituisci i campi dal database
        return [
            'meta_title'       => $model->meta_title,
            'meta_description' => $model->meta_description,
            'meta_keywords'    => $model->meta_keywords,
            'canonical_url'    => $model->canonical_url,
            'meta_robots'      => $model->meta_robots,
            'og_title'         => $model->og_title,
            'og_description'   => $model->og_description,
            'og_image'         => $model->og_image,
            'og_url'           => $model->og_url,
            'og_type'          => $model->og_type,
        ];
    }

    protected function generateHeadTags(array $seoData)
    {
        $meta = [];

        if (!empty($seoData['meta_title'])) {
            $meta[] = '<title>' . e($seoData['meta_title']) . '</title>';
            $meta[] = '<meta name="title" content="' . e($seoData['meta_title']) . '">';
        }

        if (!empty($seoData['meta_description'])) {
            $meta[] = '<meta name="description" content="' . e($seoData['meta_description']) . '">';
        }

        if (!empty($seoData['meta_keywords'])) {
            $meta[] = '<meta name="keywords" content="' . e($seoData['meta_keywords']) . '">';
        }

        if (!empty($seoData['canonical_url'])) {
            $meta[] = '<link rel="canonical" href="' . e($seoData['canonical_url']) . '">';
        }

        if (!empty($seoData['meta_robots'])) {
            $meta[] = '<meta name="robots" content="' . e($seoData['meta_robots']) . '">';
        }

        // Open Graph
        if (!empty($seoData['og_title'])) {
            $meta[] = '<meta property="og:title" content="' . e($seoData['og_title']) . '">';
        }

        if (!empty($seoData['og_description'])) {
            $meta[] = '<meta property="og:description" content="' . e($seoData['og_description']) . '">';
        }

        if (!empty($seoData['og_url'])) {
            $meta[] = '<meta property="og:url" content="' . e($seoData['og_url']) . '">';
        }

        if (!empty($seoData['og_image'])) {
            $meta[] = '<meta property="og:image" content="' . url($seoData['og_image']) . '">';
        }

        if (!empty($seoData['og_type'])) {
            $meta[] = '<meta property="og:type" content="' . e($seoData['og_type']) . '">';
        }

        return implode(PHP_EOL, $meta);
    }

}
