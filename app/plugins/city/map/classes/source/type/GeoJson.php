<?php namespace City\Map\Classes\Source\Type;

use City\Map\Classes\Feature\Collection;
use City\Map\Classes\Feature\CollectionInterface;
use City\Map\Classes\Feature\Feature;

class GeoJson extends Type
{
    const TYPE = 'geoJson';

    /**
     * @inheritDoc
     */
    public function getFeatures(): CollectionInterface
    {
        $collection = new Collection;
        $feature = (new  Feature)
            ->setType(self::TYPE);

        if ($this->source->file) {
            $feature
                ->setData('path', $this->source->file->getPath())
                ->setData('data', $this->source->file->getContents());
        }

        $collection->add($feature);

        return $collection;
    }
}
