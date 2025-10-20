<?php namespace City\Map\Components;

use City\Map\Classes\Map\DisplayInterface;
use Cms\Classes\ComponentBase;
use City\Map\Models\Map;
use Exception;

abstract class MapDetailsBase extends ComponentBase
{
    /**
     * Hide component if an error exists in map data
     * @var bool
     */
    protected $enableComponent = true;

    /**
     * Add map data
     */
    public function init()
    {
        try {
            $display = $this->getDisplay();
            $geoData = $display->getGeoData()->all();

            $this->setProperty('geoData', json_encode((array) $geoData));
            $this->setProperty('context', $display->getContext());
        } catch (Exception $e) {
            $this->enableComponent = false;
        }
    }

    /**
     * Create the component form
     */
    public function defineProperties(): array
    {
        return [
            'mapId' => [
                'title'             => 'city.map::lang.component.map',
                'description'       => 'city.map::lang.component.map_desc',
                'type'              => 'dropdown',
                'placeholder'       => trans('city.map::lang.component.select_map'),
                'required'          => true,
                'showExternalParam' => false,
            ],
            'lat' => [
                'title'             => 'city.map::lang.maps.lat',
                'description'       => 'city.map::lang.component.lat_desc',
                'type'              => 'string',
                'validationPattern' => '^[0-9.\-]+$',
                'validationMessage' => 'city.map::lang.component.lat_validation',
                'showExternalParam' => false,
            ],
            'lng' => [
                'title'             => 'city.map::lang.maps.lng',
                'description'       => 'city.map::lang.component.lng_desc',
                'type'              => 'string',
                'validationPattern' => '^[0-9.\-]+$',
                'validationMessage' => 'city.map::lang.component.lng_validation',
                'showExternalParam' => false,
            ],
            'zoom' => [
                'title'             => 'city.map::lang.maps.zoom',
                'description'       => 'city.map::lang.component.zoom_desc',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'city.map::lang.component.zoom_validation',
                'showExternalParam' => false,
            ],
            'width' => [
                'title'             => 'city.map::lang.component.width',
                'description'       => 'city.map::lang.component.width_desc',
                'required'          => true,
                'default'           => '100%',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+(px|em|%)$',
                'validationMessage' => 'city.map::lang.component.width_validation',
                'showExternalParam' => false,
            ],
            'height' => [
                'title'             => 'city.map::lang.component.height',
                'description'       => 'city.map::lang.component.height_desc',
                'required'          => true,
                'default'           => '800px',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+(px|em|%)$',
                'validationMessage' => 'city.map::lang.component.height_validation',
                'showExternalParam' => false,
            ],
        ];
    }

    /**
     * Get list with maps
     * @return array
     */
    public function getMapIdOptions(): array
    {
        $maps = Map::all();

        $options = $maps->mapWithKeys(function ($item) {
            $name = $item->name;
            if (! $item->is_active) {
                $name .= ' ' . trans('city.map::lang.maps.disabled');
            }

            return [$item->id => $name];
        });

        return $options->all();
    }

    /**
     * Prepare context of the map display
     * @return DisplayInterface
     * @throws Exception
     */
    protected function getDisplay(): DisplayInterface
    {
        $map = null;
        if ($mapId = $this->property('mapId')) {
            $map = Map::find($mapId);
        }

        if (! $map) {
            throw new Exception('Map is not found');
        }

        if (! $map->is_active) {
            throw new Exception('Map is disabled');
        }

        $display = $map->createDisplay();

        $display->getContext()
            ->setMapProvider($this->name)
            ->setComponentAlias($this->alias)
            ->setLat($this->property('lat', $map->lat))
            ->setLng($this->property('lng', $map->lng))
            ->setZoom($this->property('zoom', $map->zoom))
            ->setDate($this->property('date'));

        return $display;
    }

    /**
     * Unique ID for the map div-block
     * @return string
     */
    public function getHtmlId(): string
    {
        /**
         * Empty ID when the component template is copied to the theme
         */
        if (! $this->id) {
            $this->id = uniqid($this->name);
        }

        return str_replace($this->alias, $this->alias . '-', $this->id);
    }

    /**
     * Check if component is enabled
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool) $this->enableComponent;
    }
}
