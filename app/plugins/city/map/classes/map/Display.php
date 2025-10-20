<?php namespace City\Map\Classes\Map;

use City\Map\Models\Map;

class Display implements DisplayInterface
{
    /**
     * @var Map
     */
    protected $map;

    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var GeoDataInterface
     */
    protected $geoData;

    /**
     * Display constructor.
     * @param Map $map
     * @param ContextInterface $context
     */
    public function __construct(Map $map, ContextInterface $context)
    {
        $this->setMap($map);
        $this->setContext($context);
        $this->setGeoData(new GeoData($context));
    }

    /**
     * @inheritDoc
     */
    public function setMap(Map $map): DisplayInterface
    {
        $this->map = $map;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMap(): Map
    {
        return $this->map;
    }

    /**
     * @inheritDoc
     */
    public function setContext(ContextInterface $context): DisplayInterface
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    /**
     * @inheritDoc
     */
    public function setGeoData(GeoDataInterface $geoData): DisplayInterface
    {
        $this->geoData = $geoData;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getGeoData(): GeoDataInterface
    {
        return $this->geoData;
    }
}
