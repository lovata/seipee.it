<?php namespace City\Map\Classes\Map;

use City\Map\Classes\Feature\Collection;
use City\Map\Classes\Feature\CollectionInterface;
use City\Map\Classes\Marker\Type\Circle;
use City\Map\Classes\Marker\Type\Marker;
use City\Map\Classes\Source\Type\Custom;
use City\Map\Classes\Source\Type\GeoJson;
use Illuminate\Contracts\Support\Arrayable;

class GeoData implements GeoDataInterface, Arrayable
{
    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * GeoData constructor.
     * @param ContextInterface $context
     */
    public function __construct(ContextInterface $context)
    {
        $this->setContext($context);
    }

    /**
     * @inheritDoc
     */
    public function setContext(ContextInterface $context): GeoDataInterface
    {
        $this->context = $context;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getContext(): ContextInterface
    {
        return $this->context;
    }

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return array_merge(
            $this->markers()->toArray(),
            $this->sources()->toArray()
        );
    }

    /**
     * Retrieve collection of markers
     * @return CollectionInterface
     * @throws \City\Map\Classes\Marker\Exception\BadTypeException
     */
    protected function markers(): CollectionInterface
    {
        $collection = new Collection;
        $map = $this->getContext()->getMap();
        $markers = $map->markers->where('is_active', true);

        foreach ($markers as $marker) {
            switch ($marker->type) {
                case Marker::TYPE:
                    $marker = new Marker($marker);
                    $collection->merge($marker->getFeatures());
                    break;

                case Circle::TYPE:
                    $circle = new Circle($marker);
                    $collection->merge($circle->getFeatures());
                    break;
            }
        }

        return $collection;
    }

    /**
     * @return CollectionInterface
     * @throws \City\Map\Classes\Source\Exception\BadTypeException
     */
    protected function sources(): CollectionInterface
    {
        $collection = new Collection;
        $map = $this->getContext()->getMap();
        $sources = $map->sources->where('is_active', true);

        foreach ($sources as $source) {
            switch ($source->type) {
                case GeoJson::TYPE:
                    $geoJson = new GeoJson($source);
                    $collection->merge($geoJson->getFeatures());
                    break;

                case Custom::TYPE:
                    $customSource = new Custom($source, $this->getContext());
                    $collection->merge($customSource->getFeatures());
                    break;
            }
        }

        return $collection;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return $this->all();
    }
}
