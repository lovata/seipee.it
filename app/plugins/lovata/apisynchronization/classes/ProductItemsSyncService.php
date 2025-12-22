<?php namespace Lovata\ApiSynchronization\classes;

use Illuminate\Support\Arr;
use Lovata\Shopaholic\Models\Offer;
use Lovata\Shopaholic\Models\Product;
use Lovata\Shopaholic\Models\Category;

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
    protected ?CountryOriginPropertySyncService $countryOriginService = null;

    public function __construct(ApiClientService $api)
    {
        $this->api = $api;
        $this->countryOriginService = new CountryOriginPropertySyncService();
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
    public function sync(?string $where = null, int $rows = 200): array
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

                // Country origin field
                $countryOrigin = self::safeString($row['Nazione_Origine_merce'] ?? null);
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

                    if (!empty($countryOrigin)) {
                        $this->countryOriginService->syncProductCountryOrigin($product, $countryOrigin);
                    }

                    // Creating a default offer
                    /** @var Offer|null $offer */
                    $offer = Offer::getByProduct($product->id)->first();
                    $isNewOffer = !$offer;
                    if ($isNewOffer) {
                        $offer = new Offer();
                        $offer->product_id = $product->id;
                        $offer->active = true;
                        $offer->external_id = $extId;
                        $offer->code = $extId;
                    }

                    $offerNeedsSave = false;
                    if ($offer->name !== $name) { $offer->name = $name; $offerNeedsSave = true; }

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
                    $quantityInStock = isset($inventory[$extId]) ? (int)$inventory[$extId] : 0;
                    if ((int)$offer->quantity !== $quantityInStock) {
                        $offer->quantity = $quantityInStock;
                        $offerNeedsSave = true;
                    }

                    if ($isNewOffer) {
                        $offer->save();
                        $createdOffers++;
                    } elseif ($offerNeedsSave) {
                        $offer->save();
                        $updatedOffers++;
                    } else {
                        $skippedOffers++;
                    }

                    $processed++;
                } catch (\Throwable $e) {
                    $errors++;
                    Log::error($e);
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
