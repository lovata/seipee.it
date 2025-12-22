<?php namespace Lovata\ApiSynchronization\classes;

use Lovata\PropertiesShopaholic\Models\Property;
use Lovata\PropertiesShopaholic\Models\PropertyValue;
use Lovata\PropertiesShopaholic\Models\PropertyValueLink;
use Lovata\Shopaholic\Models\Product;

/**
 * Service for syncing Nazione_Origine_merce field as product property
 */
class CountryOriginPropertySyncService
{
    public const PROPERTY_EXTERNAL_ID = 'nazione_origine_merce';
    public const PROPERTY_NAME = 'Country of Origin';
    public const PROPERTY_CODE = 'origin_country';

    /**
     * Sync country origin property for a specific product
     *
     * @param Product $product
     * @param string|null $countryOrigin
     * @return bool Returns true if property was updated/created
     */
    public function syncProductCountryOrigin(Product $product, ?string $countryOrigin): bool
    {
        if (empty($countryOrigin)) {
            return false;
        }

        $countryOrigin = trim($countryOrigin);
        if ($countryOrigin === '') {
            return false;
        }

        $property = $this->ensureProperty();

        $propertyValue = $this->ensurePropertyValue($countryOrigin);

        $existingLink = PropertyValueLink::where([
            'product_id' => $product->id,
            'property_id' => $property->id,
            'value_id' => $propertyValue->id,
            'element_id' => $product->id,
            'element_type' => Product::class,
        ])->first();

        if ($existingLink) {
            return false;
        }

        PropertyValueLink::updateOrCreate(
            [
                'product_id'   => $product->id,
                'property_id'  => $property->id,
                'element_id'   => $product->id,
                'element_type' => Product::class,
            ],
            [
                'value_id' => $propertyValue->id,
            ]
        );


        return true;
    }

    /**
     * Ensure the country origin property exists
     */
    protected function ensureProperty(): Property
    {
        $property = Property::where('external_id', self::PROPERTY_EXTERNAL_ID)->first();

        if (!$property) {
            $property = new Property();
            $property->external_id = self::PROPERTY_EXTERNAL_ID;
            $property->name = self::PROPERTY_NAME;
            $property->code = self::PROPERTY_CODE;
            $property->type = 'select';
            $property->active = true;
            $property->save();
        }

        return $property;
    }

    /**
     * Ensure the property value exists for the given country
     */
    protected function ensurePropertyValue(string $countryOrigin): PropertyValue
    {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $countryOrigin));

        $propertyValue = PropertyValue::where('slug', $slug)->first();

        if (!$propertyValue) {
            $property = $this->ensureProperty();

            $propertyValue = new PropertyValue();
            $propertyValue->value = $countryOrigin;
            $propertyValue->save();

            $property->property_value()->attach($propertyValue->id);
        }

        return $propertyValue;
    }
}
