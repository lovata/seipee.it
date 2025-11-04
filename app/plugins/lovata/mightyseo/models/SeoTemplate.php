<?php namespace Lovata\MightySeo\Models;

use Model;
use Kharanenka\Scope\ExternalIDField;
use October\Rain\Database\Traits\Validation;
use Lovata\Toolbox\Traits\Helpers\TraitCached;

/**
 * Class SeoTemplate
 * @package Lovata\MightySeo\Models
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 *
 * @mixin \October\Rain\Database\Builder
 * @mixin \Eloquent
 *
 * @property string         $id
 * @property string         $key
 * @property string         $value
 * @property int            $external_id
 * @property string         $external_type
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $created_at
 *
 * @method static $this getByKey(string $sKey)
 * @method static $this getByExternalType(string $sKey)
 */
class SeoTemplate extends Model
{
    use Validation;
    use ExternalIDField;
    use TraitCached;

    public $table = 'lovata_mighty_seo_templates';

    public $implement = [
        '@RainLab.Translate.Behaviors.TranslatableModel',
    ];

    public $translatable = [
        'value',
    ];

    public $rules = [
        'key' => 'required|unique:lovata_mighty_seo_templates|max:255',
    ];

    public $attributeNames = [
        'key' => 'lovata.toolbox::lang.field.key',
    ];

    public $morphTo = [
        'external' => [],
    ];

    public $fillable = [
        'key',
        'value',
        'external_id',
        'external_type',
        'lang',
    ];

    public $cached = [
        'id',
        'key',
        'value',
    ];

    /**
     * Get element by key
     * @param SeoTemplate $obQuery
     * @param string      $sData
     * @return $this
     */
    public function scopeGetByKey($obQuery, $sData)
    {

        if (!empty($sData)) {
            $obQuery->where('key', $sData);
        }
        return $obQuery;
    }

    /**
     * Get element by external_type field
     * @param SeoTemplate $obQuery
     * @param string      $sData
     * @return $this
     */
    public function scopeGetByExternalType($obQuery, $sData)
    {

        if (!empty($sData)) {
            $obQuery->where('external_type', $sData);
        }
        return $obQuery;
    }

    protected function setKeyAttribute($sValue)
    {
        $this->attributes['key'] = !empty($sValue) ? trim($sValue) : null;
    }
}
