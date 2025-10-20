<?php namespace City\Map\Classes\Source\Sample;

use City\Map\Classes\Feature\Collection;
use City\Map\Classes\Feature\CollectionInterface;
use City\Map\Classes\Feature\Feature;
use City\Map\Classes\Feature\FeatureInterface;
use City\Map\Classes\Marker\Type\Marker;
use City\Map\Classes\Source\Custom\SourceBehavior;
use City\Map\Classes\Source\Custom\SourceInterface;
use October\Rain\Support\Arr;

class Places implements SourceInterface
{
    use SourceBehavior;

    const MARKERS_COUNT = 100;

    /**
     * Generate random markers
     * @return CollectionInterface
     */
    public function getGeoData(): CollectionInterface
    {
        $collection = new Collection;

        for ($i = 1; $i <= self::MARKERS_COUNT; $i++) {
            $feature = $this->getFeature();
            $collection->add($feature);
        }

        return $collection;
    }

    /**
     * Generate feature with random data
     * @return FeatureInterface
     */
    protected function getFeature(): FeatureInterface
    {
        $feature = (new Feature)
            ->setType(Marker::TYPE)
            ->addPoint($this->getRandomLat(), $this->getRandomLng())
            ->setData('marker', [
                'icon' => [
                    'iconUrl' => $this->getRandomIcon(),
                    'iconSize' => [48, 48],
                    'iconAnchor' => [24, 48],
                    'popupAnchor' => [0, -43],
                ]
            ])
            ->setData('popup', [
                'content' => $this->getRandomMessage()
            ]);

        return $feature;
    }

    /**
     * @return int
     * @throws \Exception
     */
    protected function getRandomLat(): int
    {
        $center = (int) $this->getContext()->getLat();

        $minLat = max($center - 15, -90);
        $maxLat = min($center + 15, 90);

        $lat = random_int($minLat, $maxLat);

        return $lat;
    }

    /**
     * @return int
     * @throws \Exception
     */
    protected function getRandomLng(): int
    {
        $center = (int) $this->getContext()->getLng();

        $minLng = max($center - 30, -180);
        $maxLng = min($center + 30, 180);

        $lng = random_int($minLng, $maxLng);

        return $lng;
    }

    /**
     * @return string
     */
    protected function getRandomIcon(): string
    {
        $n = random_int(1, 25);
        return url("/plugins/city/map/assets/images/places/icon$n.png");
    }

    /**
     * @return string
     */
    protected function getRandomMessage(): string
    {
        $messages = [
            'Have a good day',
            'Hello friend :)',
            'Best for you',
            'This is nice place',
            'Random place',
            '<strong>City Dynamic Maps</strong>',
        ];

        return Arr::random($messages);
    }
}
