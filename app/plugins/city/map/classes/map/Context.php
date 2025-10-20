<?php namespace City\Map\Classes\Map;

use City\Map\Models\Map;

class Context implements ContextInterface
{
    /**
     * @var Map
     */
    protected $map;

    /**
     * @var string
     */
    protected $mapProvider;

    /**
     * @var string
     */
    protected $componentAlias;

    /**
     * @var int|float
     */
    protected $lat;

    /**
     * @var int|float
     */
    protected $lng;

    /**
     * @var int
     */
    protected $zoom;

    /**
     * @var string
     */
    protected $date;

    /**
     * @inheritDoc
     */
    public function setMap(Map $map): ContextInterface
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
    public function setMapProvider(string $name): ContextInterface
    {
        $this->mapProvider = $name;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMapProvider(): string
    {
        return $this->mapProvider;
    }

    /**
     * @inheritDoc
     */
    public function setComponentAlias(string $value): ContextInterface
    {
        $this->componentAlias = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getComponentAlias(): string
    {
        return $this->componentAlias;
    }

    /**
     * @inheritDoc
     */
    public function setLat($value): ContextInterface
    {
        $this->lat = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @inheritDoc
     */
    public function setLng($value): ContextInterface
    {
        $this->lng = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @inheritDoc
     */
    public function setZoom(int $value): ContextInterface
    {
        $this->zoom = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getZoom(): int
    {
        return $this->zoom;
    }

    /**
     * @inheritDoc
     */
    public function setDate(?string $value): ContextInterface
    {
        $this->date = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * Prevents errors when object in string context
     * @return string
     */
    public function __toString(): string
    {
        return '';
    }
}
