<?php namespace City\Map\Classes\Marker\Type;

use City\Map\Classes\Marker\Exception\BadTypeException;
use City\Map\Models\Marker;

abstract class Type implements TypeInterface
{
    /**
     * @var Marker
     */
    protected $marker;

    /**
     * Type constructor.
     * @param Marker $marker
     * @throws BadTypeException
     */
    public function __construct(Marker $marker)
    {
        if ($marker->type !== static::TYPE) {
            throw new BadTypeException('Wrong marker type');
        }

        $this->marker = $marker;
    }
}
