<?php namespace Lovata\ApiSynchronization\classes;

use Illuminate\Support\Arr;
use Lovata\PropertiesShopaholic\Models\Property;
use Lovata\PropertiesShopaholic\Models\PropertyValue;
use Lovata\PropertiesShopaholic\Models\PropertyValueLink;
use Lovata\Shopaholic\Models\Product;

class ProductPropertiesSyncService extends AbstractPropertySyncService
{
    // Cache для минимизации запросов к БД
    protected array $propertiesCache = [];
    protected array $valuesCache = [];
    protected array $productsCache = [];

    /**
     * Sync properties for products using xbtvw_B2B_productVar.
     * Processes ALL properties from API:
     * - Properties in $excludedNames -> PropertyValueLink (1 to many)
     * - Properties NOT in $excludedNames -> ProductVariation + pivot (many to many)
     *
     * @param string|null $where SQL-like filter, e.g., "CodiceArticolo='900041322A03FA000025'"
     * @param int $rows
     * @param int|null $maxPages
     * @param int|null $maxItems
     * @return array
     */
    public function sync(?string $where = null, int $rows = 200, ?int $maxPages = null, ?int $maxItems = null): array
    {
        $linksCreated = 0; $linksUpdated = 0;
        $variationLinksCreated = 0;
        $skipped = 0; $missing = 0;
        $byProduct = [];
        $excludedPropertyIds = [];
        $variationPropertyIds = [];
        $clearedProducts = []; // Track products we've already detached variations for

        foreach ($this->api->paginate('xbtvw_B2B_productVar', $rows, $where, $maxPages, $maxItems) as $pageData) {
            $list = Arr::get($pageData, 'result', []);
            foreach ($list as $row) {
                $productExt = (string)($row['CodiceArticolo'] ?? '');
                if ($productExt === '') { $skipped++; continue; }
                $byProduct[$productExt] = true;

                $propCode = trim((string)($row['CodiceTipoCaratteristica'] ?? ''));
                $valueCode = trim((string)($row['CodiceCaratteristica'] ?? ''));

                if ($propCode === '' || $valueCode === '') {
                    $skipped++;
                    continue;
                }

                // Get cached or load property
                $prop = $this->getPropertyCached($propCode);
                if (!$prop) { $missing++; continue; }

                // Get cached or load value
                $value = $this->getValueCached($valueCode);
                if (!$value) { $missing++; continue; }

                // Get cached or load product
                $product = $this->getProductCached($productExt);
                if (!$product) { $missing++; continue; }

                // Detach all variations for this product on first encounter
                if (!isset($clearedProducts[$product->id])) {
                    \DB::table('lovata_shopaholic_product_variation_properties')
                        ->where('product_id', $product->id)
                        ->delete();
                    $clearedProducts[$product->id] = true;
                }

                // Check if property is in excludedNames
                $isExcluded = in_array($prop->name, $this->excludedNames ?? []);

                if ($isExcluded) {
                    // Process as regular property (1 to many)
                    $result = $this->syncRegularProperty($product, $prop, $value);
                    if ($result['created']) {
                        $linksCreated++;
                    } elseif ($result['updated']) {
                        $linksUpdated++;
                    }
                    $this->addPropertyToListIfExcluded($excludedPropertyIds, $prop);
                } else {
                    \DB::table('lovata_shopaholic_product_variation_properties')->insert([
                        'product_id' => $product->id,
                        'property_id' => $prop->id,
                        'value_id' => $value->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $variationLinksCreated++;
                    $this->addPropertyToList($variationPropertyIds, $prop);
                }
            }
        }

        // Register properties in sets
        PropertiesSyncService::addPropertiesToSet('native', array_unique($excludedPropertyIds));
        PropertiesSyncService::addPropertiesToSet('native_variations', array_unique($variationPropertyIds));

        $productsProcessed = count($byProduct);
        $productsClearedVariations = count($clearedProducts);

        return compact(
            'productsProcessed',
            'productsClearedVariations',
            'linksCreated',
            'linksUpdated',
            'variationLinksCreated',
            'skipped',
            'missing'
        );
    }

    /**
     * Get property from cache or load from DB
     */
    protected function getPropertyCached(string $externalId): ?Property
    {
        if (!isset($this->propertiesCache[$externalId])) {
            $this->propertiesCache[$externalId] = Property::where('external_id', $externalId)->first();
        }
        return $this->propertiesCache[$externalId];
    }

    /**
     * Get property value from cache or load from DB
     */
    protected function getValueCached(string $externalId): ?PropertyValue
    {
        if (!isset($this->valuesCache[$externalId])) {
            $this->valuesCache[$externalId] = PropertyValue::where('external_id', $externalId)->first();
        }
        return $this->valuesCache[$externalId];
    }

    /**
     * Get product from cache or load from DB
     */
    protected function getProductCached(string $externalId): ?Product
    {
        if (!isset($this->productsCache[$externalId])) {
            $this->productsCache[$externalId] = Product::where('external_id', $externalId)->first();
        }
        return $this->productsCache[$externalId];
    }

    /**
     * Sync regular property (1 to many via PropertyValueLink)
     */
    protected function syncRegularProperty(Product $product, Property $prop, PropertyValue $value): array
    {
        // Check if link exists
        $exists = PropertyValueLink::where([
            'product_id' => $product->id,
            'property_id' => $prop->id,
            'value_id' => $value->id,
            'element_id' => $product->id,
            'element_type' => Product::class,
        ])->first();

        if ($exists) {
            return ['created' => false, 'updated' => true];
        }

        PropertyValueLink::create([
            'product_id' => $product->id,
            'property_id' => $prop->id,
            'value_id' => $value->id,
            'element_id' => $product->id,
            'element_type' => Product::class,
        ]);

        return ['created' => true, 'updated' => false];
    }

    /**
     * Sync variation property (many to many via direct pivot insertion)
     */
    protected function syncVariationProperty(Product $product, Property $prop, PropertyValue $value, string $variationGroup): array
    {
        // Check if pivot exists
        $exists = \DB::table('lovata_shopaholic_product_variation_properties')
            ->where('product_id', $product->id)
            ->where('property_id', $prop->id)
            ->where('value_id', $value->id)
            ->exists();

        if ($exists) {
            return [
                'variationCreated' => false,
                'variationUpdated' => false,
                'linkCreated' => false,
            ];
        }

        // Insert directly into pivot table
        \DB::table('lovata_shopaholic_product_variation_properties')->insert([
            'product_id' => $product->id,
            'property_id' => $prop->id,
            'value_id' => $value->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'variationCreated' => true, // First property in this group = "variation created"
            'variationUpdated' => false,
            'linkCreated' => true,
        ];
    }
}

