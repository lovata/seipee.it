<?php

namespace Renatio\SeoManager\Models;

use Facades\Renatio\SeoManager\Classes\Htaccess;
use Facades\Renatio\SeoManager\Classes\Robots;
use October\Rain\Database\Model;
use System\Behaviors\SettingsModel;

class Settings extends Model
{
    public $implement = [SettingsModel::class];

    public $settingsCode = 'renatio_seomanager_settings';

    public $settingsFields = 'fields.yaml';

    public function initSettingsData()
    {
        $this->og_enabled = true;
    }

    public function getRobotsAttribute()
    {
        return Robots::get();
    }

    public function setRobotsAttribute($content)
    {
        Robots::set($content);
    }

    public function getHtaccessAttribute()
    {
        return Htaccess::get();
    }

    public function setHtaccessAttribute($content)
    {
        Htaccess::set($content);
    }
}
