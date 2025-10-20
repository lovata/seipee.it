<?php namespace City\Map\Classes\Source\Custom;

use City\Map\Classes\Map\ContextInterface;

trait SourceBehavior
{
    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @param ContextInterface $context
     * @return $this
     */
    public function setContext(ContextInterface $context): SourceInterface
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @return ContextInterface
     */
    public function getContext(): ContextInterface
    {
        return $this->context;
    }
}
