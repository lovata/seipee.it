<?php namespace Lovata\MightySeo\Models;

use Lovata\Toolbox\Models\CommonSettings;
use Tailor\Classes\Blueprint;

/**
 * Class Settings
 * @package Lovata\MightySeo\Models
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 *
 * @mixin \October\Rain\Database\Builder
 * @mixin \Eloquent
 * @mixin \System\Behaviors\SettingsModel
 */
class Settings extends CommonSettings
{
    const SETTINGS_CODE = 'lovata_mighty_seo_settings';
    public $settingsCode = 'lovata_mighty_seo_settings';

    public $translatable = [
        'seo_title_prefix',
        'seo_title_suffix',
    ];

    public function getEnabledBlueprintEntryOptions()
    {
        $arOptions = [];
        $arBlueprintList = Blueprint::listInProject();
        foreach ($arBlueprintList as $obBlueprint) {
            if (in_array($obBlueprint->type, ['mixin'])) {
                continue;
            }

            $arOptions[$obBlueprint->handle] = $obBlueprint->name;
        }

        return $arOptions;
    }
}
