<?php namespace City\Map\Classes\Feature;

use Illuminate\Contracts\Support\Arrayable;

interface FeatureInterface extends Arrayable
{
    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type): self;

    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param int|float $lat
     * @param int|float $lng
     * @return $this
     */
    public function addPoint($lat, $lng): self;

    /**
     * @return array
     */
    public function getPoints(): array;

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setData(string $key, $value): self;

    /**
     * @param string|null $key
     * @return $this
     */
    public function getData(?string $key = null);
}
