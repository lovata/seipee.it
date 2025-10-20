<?php namespace City\Map\Classes\Marker\Type;

use City\Map\Classes\Feature\Collection;
use City\Map\Classes\Feature\CollectionInterface;
use City\Map\Classes\Feature\Feature;

class Circle extends Type
{
    const TYPE = 'circle';

    /**
     * @inheritDoc
     */
    public function getFeatures(): CollectionInterface
    {
        $collection = new Collection;
        $feature = (new  Feature)
            ->setType(self::TYPE)
            ->addPoint($this->marker->lat, $this->marker->lng)
            ->setData('circle', [
                'color' => $this->marker->color,
                'radius' => (int) $this->marker->size,
            ]);

        $collection->add($feature);

        return $collection;
    }
}
