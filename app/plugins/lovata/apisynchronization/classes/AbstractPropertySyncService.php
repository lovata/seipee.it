<?php namespace Lovata\ApiSynchronization\classes;

use Lovata\PropertiesShopaholic\Models\Property;

/**
 * Abstract base class for property synchronization services
 * Contains common logic for filtering excluded properties
 */
abstract class AbstractPropertySyncService
{
    protected ApiClientService $api;

    /**
     * List of property names to exclude from synchronization
     * @var array
     */
    protected array $excludedNames = [
        'MOTORI',
        'POLARITÀ',
        'GRANDEZZA MOTORE',
        'FORMA',
        'SERIE',
        'POTENZA',
        'VOLTAGGIO',
        'SIGLA FORNITORE',
    ];

    public function __construct(ApiClientService $api)
    {
        $this->api = $api;
    }

    /**
     * Add property to list if not excluded (for variations - everything EXCEPT excludedNames)
     *
     * @param array    $propertyIds
     * @param Property $property
     * @return void
     */
    protected function addPropertyToList(array &$propertyIds, Property $property): void
    {
        if (!in_array($property->id, $propertyIds) && !in_array($property->name, $this->excludedNames ?? [])) {
            $propertyIds[] = $property->id;
        }
    }

    /**
     * Add property to list if excluded (for regular properties - ONLY excludedNames)
     *
     * @param array    $propertyIds
     * @param Property $property
     * @return void
     */
    protected function addPropertyToListIfExcluded(array &$propertyIds, Property $property): void
    {
        if (!in_array($property->id, $propertyIds) && in_array($property->name, $this->excludedNames ?? [])) {
            $propertyIds[] = $property->id;
        }
    }

    /**
     * Abstract method to be implemented by child classes
     *
     * @param string|null $where
     * @param int $rows
     * @param int|null $maxPages
     * @param int|null $maxItems
     * @return array
     */
    abstract public function sync(?string $where = null, int $rows = 200, ?int $maxPages = null, ?int $maxItems = null): array;
}
