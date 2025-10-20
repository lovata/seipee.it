<?php

namespace Renatio\SeoManager\Components;

use Cms\Classes\ComponentBase;
use Exception;
use Media\Classes\MediaLibrary;
use October\Rain\Database\Model;
use October\Rain\Support\Facades\Event;
use Renatio\SeoManager\Behaviors\SeoModel;
use Renatio\SeoManager\Models\Settings;
use Tailor\Classes\ComponentVariable;
use Tailor\Models\EntryRecord;

class SeoTags extends ComponentBase
{
    public $seoTag;

    public $settings;

    public function componentDetails()
    {
        return [
            'name' => 'renatio.seomanager::lang.component.name',
            'description' => 'renatio.seomanager::lang.component.description',
        ];
    }

    public function onRender()
    {
        $this->seoTag = $this->getSeoModelFromController();
        $this->settings = Settings::instance();

        if (! $this->seoTag) {
            $this->seoTag = $this->getSeoTagFromPage();
        }

        Event::fire('seo.beforeComponentRender', [$this, $this->page]);

        $this->prepareSeoTag();

        $this->setPageVars();
    }

    protected function getSeoModelFromController()
    {
        foreach ($this->controller->vars as $var) {
            if (
                $var instanceof ComponentVariable
                && $var->getRecord() instanceof EntryRecord
                && isset($var->getRecord()->is_seo_manager)
            ) {
                return $var->getRecord();
            }

            if (! ($var instanceof Model)) {
                continue;
            }

            if (! $var->isClassExtendedWith(SeoModel::class)) {
                continue;
            }

            return $var;
        }
    }

    protected function getSeoTagFromPage()
    {
        Model::unguard();

        $seoTag = new Model;

        $data = $this->page;

        if (empty($this->page->seo_title) && ! empty($this->page->viewBag->seo_title)) {
            $data = $this->page->viewBag;
        }

        $seoTag->fill([
            'meta_title' => $data->seo_title ?? $data->meta_title,
            'meta_description' => $data->seo_description ?? $data->meta_description,
            'meta_keywords' => $data->meta_keywords,
            'robot_index' => $data->robot_index,
            'robot_follow' => $data->robot_follow,
            'robot_advanced' => $data->robot_advanced,
            'canonical_url' => $data->canonical_url,
            'redirect_url' => $data->redirect_url,
            'og_title' => $data->og_title,
            'og_type' => $data->og_type,
            'og_description' => $data->og_description,
            'og_image' => $data->og_image,
        ]);

        Model::reguard();

        return $seoTag;
    }

    protected function prepareSeoTag()
    {
        $this->setOgImage();

        $this->seoTag->title = $this->getTitle();

        $this->setRobots();
    }

    protected function setOgImage()
    {
        if (! $this->seoTag->og_image) {
            return;
        }

        try {
            $this->seoTag->og_image_url = MediaLibrary::url($this->seoTag->og_image);
        } catch (Exception $e) {
            $this->seoTag->og_image_url = $this->seoTag->og_image;
        }

        $this->setOgImageDimensions();
    }

    protected function setOgImageDimensions()
    {
        try {
            [$width, $height] = getimagesize($this->seoTag->og_image_url);

            $this->seoTag->og_image_width = $width;
            $this->seoTag->og_image_height = $height;
        } catch (Exception $e) {
            $this->seoTag->og_image_width = null;
            $this->seoTag->og_image_height = null;
        }
    }

    protected function getTitle()
    {
        return trim(
            implode(' ', [
                $this->settings->title_prefix,
                $this->seoTag->meta_title,
                $this->settings->title_suffix,
            ])
        );
    }

    protected function setRobots()
    {
        $this->seoTag->robots = (optional($this->seoTag)->robot_index ?? 'index')
            .', '
            .(optional($this->seoTag)->robot_follow ?? 'follow');

        if (optional($this->seoTag)->robot_advanced) {
            $this->seoTag->robots .= ', '.$this->seoTag->robot_advanced;
        }
    }

    protected function setPageVars()
    {
        $this->page['seoTag'] = $this->seoTag;

        $this->page['currentUrl'] = request()->path();

        $this->page['seoSettings'] = $this->settings;
    }
}
