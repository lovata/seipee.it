<?php namespace City\Map\Models;

use City\Map\Classes\Source\Type\Custom;
use City\Map\Classes\Source\Type\GeoJson;
use Model;

/**
 * Model
 */
class Source extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model
     */
    public $table = 'city_map_sources';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'is_active'             => 'required|boolean',
        'name'                  => 'required|between:1,200',
        'type'                  => 'required|in:geoJson,custom',
        'value'                 => 'required_if:type,custom|between:1,200',
    ];

    /**
     * @var array Relation between source and map
     */
    public $morphToMany = [
        'maps' => [
            Map::class,
            'table' => 'city_map_relations',
            'name' => 'relation',
        ],
    ];

    /**
     * @var string[] File attachments
     */
    public $attachOne = [
        'file' => 'System\Models\File'
    ];

    /**
     * @return array
     */
    public function getTypeOptions(): array
    {
        return [
            GeoJson::TYPE => trans('city.map::lang.sources.type_option.geojson'),
            Custom::TYPE => trans('city.map::lang.sources.type_option.custom'),
        ];
    }
}
