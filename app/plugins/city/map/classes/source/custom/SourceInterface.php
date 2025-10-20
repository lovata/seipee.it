<?php namespace City\Map\Classes\Source\Custom;

use City\Map\Classes\Feature\CollectionInterface;
use City\Map\Classes\Map\ContextInterface;

interface SourceInterface
{
    /**
     * Get prepared data for map
     * @return CollectionInterface
     */
    public function getGeoData(): CollectionInterface;

    /**
     * @param ContextInterface $context
     * @return $this
     */
    public function setContext(ContextInterface $context): self;

    /**
     * @return ContextInterface
     */
    public function getContext(): ContextInterface;
}
