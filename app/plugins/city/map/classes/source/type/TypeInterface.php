<?php namespace City\Map\Classes\Source\Type;

use City\Map\Classes\Feature\CollectionInterface;

interface TypeInterface
{
    /**
     * @return CollectionInterface
     */
    public function getFeatures(): CollectionInterface;
}
