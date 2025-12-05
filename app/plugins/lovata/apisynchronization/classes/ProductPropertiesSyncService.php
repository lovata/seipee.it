<?php namespace Lovata\ApiSynchronization\classes;

use Illuminate\Support\Arr;
use Lovata\PropertiesShopaholic\Models\Property;
use Lovata\PropertiesShopaholic\Models\PropertyValue;
use Lovata\PropertiesShopaholic\Models\PropertyValueLink;
use Lovata\Shopaholic\Models\Product;

class ProductPropertiesSyncService
{
    protected ApiClientService $api;

    public function __construct(ApiClientService $api)
    {
        $this->api = $api;
    }

    /**
     * Sync properties for products using xbtvw_B2B_productVar.
     * This attaches PropertyValue links when the API row contains a value external code `CodiceCaratteristica`.
     * If only group `Codice` is present (no specific value), the property is ensured to exist but no link is created.
     *
     * @param string|null $where SQL-like filter, e.g., "CodiceArticolo='900041322A03FA000025'"
     * @param int $rows
     * @param int|null $maxPages
     * @return array
     */
    public function sync(?string $where = null, int $rows = 200, ?int $maxPages = null, ?int $maxItems = null): array
    {
        $productsProcessed = 0; $linksCreated = 0; $linksUpdated = 0; $skipped = 0; $missing = 0; $processed = 0;
        $byProduct = [];

        foreach ($this->api->paginate('xbtvw_B2B_productVar', $rows, $where, $maxPages, $maxItems) as $pageData) {
            $list = Arr::get($pageData, 'result', []);
            foreach ($list as $row) {
                $productExt = (string)($row['CodiceArticolo'] ?? '');
                if ($productExt === '') { $skipped++; continue; }
                $byProduct[$productExt] = true;

                $propCode = trim((string)($row['CodiceTipoCaratteristica'] ?? ''));
                $prop = null;
                if ($propCode !== '') {
                    $prop = Property::where('external_id', $propCode)->first();
                }

                $valueCode = trim((string)($row['CodiceCaratteristica'] ?? ''));
                if ($valueCode !== '' && $prop) {
                    $value = PropertyValue::where('external_id', $valueCode)->first();
                    $product = Product::where('external_id', $productExt)->first();

                    if (!$product) { $missing++; $processed++; continue; }
                    if (!$value)   { $missing++; $processed++; continue; }

                    // Check if link exists
                    $exists = PropertyValueLink::where([
                        'product_id' => $product->id,
                        'property_id'=> $prop->id,
                        'value_id'   => $value->id,
                        'element_id' => $product->id,
                        'element_type' => Product::class,
                    ])->first();

                    if ($exists) {
                        $linksUpdated++; // idempotent; nothing to change
                    } else {
                        PropertyValueLink::create([
                            'product_id' => $product->id,
                            'property_id'=> $prop->id,
                            'value_id'   => $value->id,
                            'element_id' => $product->id,
                            'element_type' => Product::class,
                        ]);
                        $linksCreated++;
                    }
                    $processed++;
                } else {
                    // No specific value in the row; cannot create link
                    $skipped++;
                }
            }
        }

        $productsProcessed = count($byProduct);
        return compact('productsProcessed','linksCreated','linksUpdated','skipped','missing');
    }
}
