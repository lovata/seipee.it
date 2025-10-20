<?php namespace City\Map\Classes\Feature;

class Feature implements FeatureInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $points = [];

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @inheritDoc
     */
    public function setType($type): FeatureInterface
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function addPoint($lat, $lng): FeatureInterface
    {
        $this->points[] = [$lat, $lng];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPoints(): array
    {
        return $this->points;
    }

    /**
     * @inheritDoc
     */
    public function setData(string $key, $value): FeatureInterface
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getData(?string $key = null)
    {
        if (null !== $key) {
            return $this->data[$key] ?? null;
        }

        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = [
            'type' => $this->getType(),
        ];

        if ($points = $this->getPoints()) {
            $data['points'] = $points;
        }

        return array_merge($data, $this->getData());
    }
}
