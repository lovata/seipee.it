<?php namespace City\Map\Models;

use City\Map\Classes\Marker\Type\Circle;
use Model;
use City\Map\Classes\Marker\Type\Marker as MarkerType;

/**
 * Model
 */
class Marker extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model
     */
    public $table = 'city_map_markers';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'is_active'             => 'required|boolean',
        'name'                  => 'required|between:1,200',
        'type'                  => 'required|in:marker,circle',
        'lat'                   => 'required|numeric|between:-90,90',
        'lng'                   => 'required|numeric|between:-180,180',
        'image'                 => 'between:0,250',
        'size'                  => 'required_if:type,circle|integer|min:1',
    ];

    /**
     * @var string[] Change field type
     */
    public $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    /**
     * @var array Relation between marker and map
     */
    public $morphToMany = [
        'maps' => [
            Map::class,
            'table' => 'city_map_relations',
            'name' => 'relation',
        ],
    ];

    /**
     * @return array
     */
    public function getTypeOptions(): array
    {
        return [
            MarkerType::TYPE => trans('city.map::lang.markers.type_option.marker'),
            Circle::TYPE => trans('city.map::lang.markers.type_option.circle'),
        ];
    }
}
