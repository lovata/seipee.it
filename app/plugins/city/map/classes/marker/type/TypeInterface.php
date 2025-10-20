<?php namespace City\Map\Classes\Marker\Type;

use City\Map\Classes\Feature\CollectionInterface;

interface TypeInterface
{
    /**
     * @return CollectionInterface
     */
    public function getFeatures(): CollectionInterface;
}
