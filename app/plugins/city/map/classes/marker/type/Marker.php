<?php namespace City\Map\Classes\Marker\Type;

use City\Map\Classes\Feature\Collection;
use City\Map\Classes\Feature\CollectionInterface;
use City\Map\Classes\Feature\Feature;

class Marker extends Type
{
    const TYPE = 'marker';

    /**
     * @inheritDoc
     */
    public function getFeatures(): CollectionInterface
    {
        $collection = new Collection;
        $feature = (new  Feature)
            ->setType(self::TYPE)
            ->addPoint($this->marker->lat, $this->marker->lng);

        $markerData = [
            'color' => $this->marker->color,
        ];

        if ($this->marker->image) {
            $storagePath = config(
                'system.storage.media.path',
                config('cms.storage.media.path')
            );

            $markerData['icon'] = [
                'iconUrl' => url($storagePath) . $this->marker->image
            ];

            // Get the size
            $imagePath = storage_path('app/media') . $this->marker->image;
            list ($width, $height) = getimagesize($imagePath);

            if (! $width || ! $height) {
                $width = $height = 48;
            }

            // OSM params
            $markerData['icon']['iconSize'] = [$width, $height];
            $markerData['icon']['iconAnchor'] = [round($width / 2, 0), $height];
            $markerData['icon']['popupAnchor'] = [0, -($height - 5)];
        }

        $feature
            ->setData('marker', $markerData)
            ->setData('popup', [
                'content' => $this->marker->description
            ]);

        $collection->add($feature);

        return $collection;
    }
}
