<?php namespace City\Map\Classes\Feature;

use Illuminate\Contracts\Support\Arrayable;

interface CollectionInterface extends Arrayable
{
    /**
     * @param FeatureInterface $source
     * @return $this
     */
    public function add(FeatureInterface $source): self;

    /**
     * Get all items
     * @return array
     */
    public function all(): array;

    /**
     * Count of items
     * @return int
     */
    public function count(): int;

    /**
     * @param CollectionInterface $collection
     * @return $this
     */
    public function merge(self $collection): self;
}
