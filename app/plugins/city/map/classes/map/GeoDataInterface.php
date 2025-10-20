<?php namespace City\Map\Classes\Map;

interface GeoDataInterface
{
    /**
     * @param ContextInterface $context
     * @return $this
     */
    public function setContext(ContextInterface $context): self;

    /**
     * @return ContextInterface
     */
    public function getContext(): ContextInterface;

    /**
     * Retrieve all data based on markers and sourced
     * @return array
     */
    public function all(): array;
}
