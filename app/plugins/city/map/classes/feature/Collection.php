<?php namespace City\Map\Classes\Feature;

class Collection implements CollectionInterface
{
    /**
     * @var \October\Rain\Support\Collection
     */
    protected $items;

    /**
     * Create ampty collection
     */
    public function __construct()
    {
        $this->items = collect();
    }

    /**
     * @inheritDoc
     */
    public function add(FeatureInterface $source): CollectionInterface
    {
        $this->items->push($source);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->items->all();
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->items->count();
    }

    /**
     * @inheritDoc
     */
    public function merge(CollectionInterface $collection): CollectionInterface
    {
        foreach ($collection->all() as $item) {
            $this->add($item);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->items->toArray();
    }
}
