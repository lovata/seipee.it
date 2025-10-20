<?php namespace City\Map\Classes\Map;

use City\Map\Models\Map;

interface ContextInterface
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
     * @param string $name
     * @return $this
     */
    public function setMapProvider(string $name): self;

    /**
     * @return string
     */
    public function getMapProvider(): string;

    /**
     * @param string $value
     * @return $this
     */
    public function setComponentAlias(string $value): self;

    /**
     * @return string
     */
    public function getComponentAlias(): string;

    /**
     * @param $value
     * @return $this
     */
    public function setLat($value): self;

    /**
     * @return mixed
     */
    public function getLat();

    /**
     * @param $value
     * @return $this
     */
    public function setLng($value): self;

    /**
     * @return mixed
     */
    public function getLng();

    /**
     * @param int $value
     * @return $this
     */
    public function setZoom(int $value): self;

    /**
     * @return int
     */
    public function getZoom(): int;

    /**
     * @param string|null $value
     * @return $this
     */
    public function setDate(?string $value): self;

    /**
     * @return string|null
     */
    public function getDate(): ?string;
}
