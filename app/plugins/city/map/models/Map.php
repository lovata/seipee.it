<?php namespace City\Map\Models;

use City\Map\Classes\Map\Display;
use City\Map\Classes\Map\DisplayInterface;
use City\Map\Classes\Map\Context;
use Model;

/**
 * Model
 */
class Map extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model
     */
    public $table = 'city_map_maps';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'is_active'             => 'required|boolean',
        'name'                  => 'required|between:1,200',
        'lat'                   => 'required|numeric|between:-90,90',
        'lng'                   => 'required|numeric|between:-180,180',
        'zoom'                  => 'required|integer|between:0,30',
    ];

    /**
     * @var string[] Change field type
     */
    public $casts = [
        'lat' => 'float',
        'lng' => 'float',
    ];

    /**
     * @var array
     */
    public $morphedByMany = [
        'markers' => [
            Marker::class,
            'table' => 'city_map_relations',
            'name' => 'relation',
        ],
        'sources' => [
            Source::class,
            'table' => 'city_map_relations',
            'name' => 'relation',
        ],
    ];

    /**
     * Zoom levels
     * @return array
     */
    public function getZoomOptions()
    {
        return range(0, 25);
    }

    /**
     * @return DisplayInterface
     */
    public function createDisplay(): DisplayInterface
    {
        $context = (new Context)
            ->setMap($this)
            ->setLat($this->lat)
            ->setLng($this->lng)
            ->setZoom($this->zoom);

        $display = new Display($this, $context);

        return $display;
    }
}
