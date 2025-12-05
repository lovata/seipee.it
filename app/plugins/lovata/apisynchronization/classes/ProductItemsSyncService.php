<?php namespace Lovata\ApiSynchronization\classes;

use Illuminate\Support\Arr;
use Lovata\Shopaholic\Models\Offer;
use Lovata\Shopaholic\Models\Product;
use Lovata\Shopaholic\Models\Category;
use Lovata\PropertiesShopaholic\Models\PropertyValueLink;

/**
 * ProductItemsSyncService
 *
 * Renamed from ItemsSyncService to follow the requested naming (products vs items).
 * Functionality is identical: syncs Seipee Items (xbtvw_B2B_product) into Shopaholic Products + default Offer.
 */
class ProductItemsSyncService
{
    protected ApiClientService $api;
    protected static ?int $defaultCategoryId = null;

    // Base properties that are part of the product core, not used for offer generation
    const BASE_PROPERTIES = ['S0001', 'S0002', 'S0003', 'S0004', 'S0006', 'S0007', 'V0000'];

    public function __construct(ApiClientService $api)
    {
        $this->api = $api;
    }

    protected function getDefaultCategoryId(): int
    {
        if (self::$defaultCategoryId !== null) {
            return self::$defaultCategoryId;
        }
        $category = Category::where('code', 'default')->first();
        if (!$category) {
            $category = new Category();
            $category->name = 'Default';
            $category->slug = 'default';
            $category->code = 'default';
            $category->active = true;
            $category->save();
        }
        self::$defaultCategoryId = (int) $category->id;
        return self::$defaultCategoryId;
    }

    /**
     * Sync products from Seipee API table xbtvw_B2B_product.
     * - Upserts Product by external_id = CodiceArticolo
     * - Ensures one default Offer per product with price and dimensions
     * - Fetches inventory data from warehouse endpoints and updates offer quantities
     * - Saves only when data changed; supports dry-run and item cap
     *
     * @param string|null $where
     * @param int $rows
     * @return array
     */
    public function sync(?string $where = null, int $rows = 20): array
    {
        $createdProducts = 0; $updatedProducts = 0; $skippedProducts = 0;
        $createdOffers = 0; $updatedOffers = 0; $skippedOffers = 0; $errors = 0; $processed = 0;

        // Fetch inventory data from both warehouse endpoints
        $inventoryService = new InventorySyncService($this->api);
        $inventory = $inventoryService->fetchInventory($rows);

        foreach ($this->api->paginate('xbtvw_B2B_product', $rows, $where) as $pageData) {
            $list = Arr::get($pageData, 'result', []);
            foreach ($list as $row) {
                $extId = self::safeString($row['CodiceArticolo'] ?? null);
                $name  = self::safeString($row['Descrizione'] ?? $extId);
                if ($extId === '') { $skippedProducts++; $processed++; continue; }

                // Normalize numeric fields
                $width  = self::toFloat($row['Larghezza'] ?? null);
                $length = self::toFloat($row['Lunghezza'] ?? null);
                $height = self::toFloat($row['Altezza'] ?? null);
                $weight = self::toFloat($row['PesoNetto'] ?? null);
                $price  = self::toFloat($row['Prezzo'] ?? null);

                //For future, not usable rn
                $packaging = self::safeString($row['Tipo_imballo'] ?? null);
                $vatCode   = self::safeString($row['CodiceIva'] ?? null);

                try {
                    /** @var Product|null $product */
                    $product = Product::where('external_id', $extId)->first();
                    $isNewProduct = !$product;
                    if ($isNewProduct) {
                        $product = new Product();
                        $product->external_id = $extId;
                        $product->active = true;
                    }

                    // Detect changes on product
                    $productNeedsSave = false;
                    if ($product->name !== $name) { $product->name = $name; $productNeedsSave = true; }
                    if (empty($product->code)) { $product->code = $extId; $productNeedsSave = true; }
                    if (empty($product->category_id)) { $product->category_id = $this->getDefaultCategoryId(); $productNeedsSave = true; }

                    if ($isNewProduct) {
                        $product->save();
                        $createdProducts++;
                    } elseif ($productNeedsSave) {
                        $product->save();
                        $updatedProducts++;
                    } else {
                        $skippedProducts++;
                    }

                    // Generate offers based on variant properties
                    $variantProperties = $this->getVariantProperties($product->id);

                    if (empty($variantProperties)) {
                        // No variant properties - create single default offer
                        $offerStats = $this->createOrUpdateOffer($product, $extId, $name, null, $price, $width, $length, $height, $weight, $inventory);
                        $createdOffers += $offerStats['created'];
                        $updatedOffers += $offerStats['updated'];
                        $skippedOffers += $offerStats['skipped'];
                    } else {
                        // Generate offers for each individual variant property
                        foreach ($variantProperties as $propData) {
                            $offerStats = $this->createOrUpdateOffer($product, $extId, $name, [$propData], $price, $width, $length, $height, $weight, $inventory);
                            $createdOffers += $offerStats['created'];
                            $updatedOffers += $offerStats['updated'];
                            $skippedOffers += $offerStats['skipped'];
                        }

                        // Generate combined offers for pairs of variant properties
                        if (count($variantProperties) >= 2) {
                            for ($i = 0; $i < count($variantProperties); $i++) {
                                for ($j = $i + 1; $j < count($variantProperties); $j++) {
                                    $offerStats = $this->createOrUpdateOffer($product, $extId, $name, [$variantProperties[$i], $variantProperties[$j]], $price, $width, $length, $height, $weight, $inventory);
                                    $createdOffers += $offerStats['created'];
                                    $updatedOffers += $offerStats['updated'];
                                    $skippedOffers += $offerStats['skipped'];
                                }
                            }
                        }
                    }

                    $processed++;
                } catch (\Throwable $e) {
                    $errors++;
                }
            }
            print_r('Batch processed');
        }

        return compact('createdProducts','updatedProducts','skippedProducts','createdOffers','updatedOffers','skippedOffers','errors');
    }

    protected static function toFloat($value): ?float
    {
        if ($value === null || $value === '') { return null; }
        if (is_array($value) || is_object($value)) { return null; }
        if (is_numeric($value)) { return (float)$value; }
        // Replace comma with dot
        $v = str_replace([' ', ','], ['', '.'], (string)$value);
        return is_numeric($v) ? (float)$v : null;
    }

    protected static function safeString($value): string
    {
        if ($value === null) { return ''; }
        if (is_array($value) || is_object($value)) { return ''; }
        $s = (string)$value;
        return trim($s);
    }

    /**
     * Get variant (non-base) properties for a product
     * Returns array of property data: [['property_code' => 'S0005', 'value_code' => 'VAL1', 'value_name' => 'Value 1'], ...]
     *
     * @param int $productId
     * @return array
     */
    protected function getVariantProperties(int $productId): array
    {
        $links = PropertyValueLink::where('product_id', $productId)
            ->where('element_type', Product::class)
            ->where('element_id', $productId)
            ->with(['property', 'value'])
            ->get();

        $variantProperties = [];
        foreach ($links as $link) {
            if (!$link->property || !$link->value) {
                continue;
            }

            $propertyCode = $link->property->external_id ?? $link->property->code ?? null;
            if (!$propertyCode || in_array($propertyCode, self::BASE_PROPERTIES)) {
                continue;
            }

            $variantProperties[] = [
                'property_code' => $propertyCode,
                'property_name' => $link->property->name ?? $propertyCode,
                'value_code' => $link->value->external_id ?? $link->value->slug ?? null,
                'value_name' => $link->value->value ?? $link->value->label ?? 'Unknown',
            ];
        }

        return $variantProperties;
    }

    /**
     * Create or update an offer for a product with optional property combination
     *
     * @param Product $product
     * @param string $productExtId
     * @param string $baseName
     * @param array|null $propertyCombo Array of property data or null for default offer
     * @param float|null $price
     * @param float|null $width
     * @param float|null $length
     * @param float|null $height
     * @param float|null $weight
     * @param array $inventory
     * @return array ['created' => int, 'updated' => int, 'skipped' => int]
     */
    protected function createOrUpdateOffer(Product $product, string $productExtId, string $baseName, ?array $propertyCombo, ?float $price, ?float $width, ?float $length, ?float $height, ?float $weight, array $inventory): array
    {
        // Generate offer identifiers and name based on property combination
        if ($propertyCombo === null) {
            // Default offer
            $offerExternalId = $productExtId . '-default';
            $offerCode = $productExtId;
            $offerName = $baseName;
        } else {
            // Property-based offer
            $propertyCodes = array_column($propertyCombo, 'property_code');
            $valueNames = array_column($propertyCombo, 'value_name');

            sort($propertyCodes); // Ensure consistent ordering
            $offerExternalId = $productExtId . '-' . implode('-', $propertyCodes);
            $offerCode = $productExtId . '-' . implode('-', $propertyCodes);
            $offerName = $baseName . ' (' . implode(' + ', $valueNames) . ')';
        }

        // Find or create offer
        $offer = Offer::where('external_id', $offerExternalId)->first();
        $isNewOffer = !$offer;

        if ($isNewOffer) {
            $offer = new Offer();
            $offer->product_id = $product->id;
            $offer->active = true;
            $offer->external_id = $offerExternalId;
            $offer->code = $offerCode;
        }

        // Detect changes
        $offerNeedsSave = false;
        if ($offer->name !== $offerName) { $offer->name = $offerName; $offerNeedsSave = true; }

        $currentPrice = $offer->main_price ? (float)$offer->main_price->price_value : null;
        if ($price !== null && (string)$currentPrice !== (string)$price) {
            $offer->price = $price;
            $offerNeedsSave = true;
        }

        // Dimensions/weight
        if ($width !== null && (string)$offer->width !== (string)$width) { $offer->width = $width; $offerNeedsSave = true; }
        if ($length !== null && (string)$offer->length !== (string)$length) { $offer->length = $length; $offerNeedsSave = true; }
        if ($height !== null && (string)$offer->height !== (string)$height) { $offer->height = $height; $offerNeedsSave = true; }
        if ($weight !== null && (string)$offer->weight !== (string)$weight) { $offer->weight = $weight; $offerNeedsSave = true; }

        // Update quantity from inventory data
        $quantityInStock = isset($inventory[$productExtId]) ? (int)$inventory[$productExtId] : 0;

        if ((int)$offer->quantity !== $quantityInStock) {
            $offer->quantity = $quantityInStock;
            $offerNeedsSave = true;
        }

        if ($isNewOffer) {
            $offer->save();
            return ['created' => 1, 'updated' => 0, 'skipped' => 0];
        } elseif ($offerNeedsSave) {
            $offer->save();
            return ['created' => 0, 'updated' => 1, 'skipped' => 0];
        } else {
            return ['created' => 0, 'updated' => 0, 'skipped' => 1];
        }
    }

    //for future, not usable rn
    protected function buildPreviewText(?string $existing, string $packaging, string $vatCode): ?string
    {
        $parts = [];
        if ($packaging !== '') { $parts[] = 'Package: '.$packaging; }
        if ($vatCode !== '') { $parts[] = 'VAT: '.$vatCode; }
        $suffix = implode(' | ', $parts);
        if ($suffix === '') { return $existing; }
        $base = (string)($existing ?? '');
        // Remove previous tags if present
        $base = trim(preg_replace('/\s*\|\s*(Package: .*?)(\s*\|\s*VAT: .*?)?$/i', '', $base));
        return trim($base.($base !== '' ? ' | ' : '').$suffix);
    }
}
