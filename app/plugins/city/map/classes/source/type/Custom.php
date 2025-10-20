<?php namespace City\Map\Classes\Source\Type;

use City\Map\Classes\Feature\Collection;
use City\Map\Classes\Feature\CollectionInterface;
use City\Map\Classes\Map\ContextInterface;
use City\Map\Classes\Source\Custom\SourceInterface;
use City\Map\Classes\Source\Exception\BadCustomSourceException;
use City\Map\Models\Source;

class Custom extends Type
{
    const TYPE = 'custom';

    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @param Source $source
     * @param ContextInterface $context
     * @throws \City\Map\Classes\Source\Exception\BadTypeException
     */
    public function __construct(Source $source, ContextInterface $context)
    {
        parent::__construct($source);
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function getFeatures(): CollectionInterface
    {
        try {
            $source = $this->getSourceInstance();
            $collection = $source->getGeoData();
        } catch (BadCustomSourceException $e) {
            $collection = new Collection;
        }

        return $collection;
    }

    /**
     * Create class instance
     * @return SourceInterface
     * @throws BadCustomSourceException
     */
    protected function getSourceInstance(): SourceInterface
    {
        $className = $this->source->value;

        if (! $className || ! class_exists($className)) {
            throw new BadCustomSourceException('Class of custom source is not found');
        }

        $object = new $className;

        if (! $object instanceof SourceInterface) {
            throw new BadCustomSourceException(
                'Class of custom source must implement City\Map\Classes\Source\Custom\SourceInterface'
            );
        }

        $object->setContext($this->context);

        return $object;
    }
}
