<?php namespace City\Map\Classes\Map;

use City\Map\Models\Map;

interface DisplayInterface
{
    /**
     * @param Map $map
     * @return $this
     */
    public function setMap(Map $map): self;

    /**
     * @return Map
     */
    public function getMap(): Map;

    /**
     * @param ContextInterface $context
     * @return $this
     */
    public function setContext(ContextInterface $context): self;

    /**
     * @return ContextInterface
     */
    public function getContext(): ContextInterface;

    /**
     * @param GeoDataInterface $geoData
     * @return $this
     */
    public function setGeoData(GeoDataInterface $geoData): self;

    /**
     * @return GeoDataInterface
     */
    public function getGeoData(): GeoDataInterface;
}
