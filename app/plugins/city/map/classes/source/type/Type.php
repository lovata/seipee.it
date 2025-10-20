<?php namespace City\Map\Classes\Source\Type;

use City\Map\Classes\Source\Exception\BadTypeException;
use City\Map\Models\Source;

abstract class Type implements TypeInterface
{
    /**
     * @var Source
     */
    protected $source;

    /**
     * Type constructor.
     * @param Source $source
     */
    public function __construct(Source $source)
    {
        if ($source->type !== static::TYPE) {
            throw new BadTypeException('Wrong source type');
        }

        $this->source = $source;
    }
}
