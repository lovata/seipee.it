<?php namespace Lovata\ApiSynchronization\classes;

use Illuminate\Support\Arr;
use Lovata\Shopaholic\Models\Offer;
use Log;

/**
 * InventorySyncService
 *
 * Fetches inventory data from two Seipee API endpoints:
 * - xbtvw_B2B_Giac (internal warehouse stock)
 * - xbtvw_B2B_GiacCD (external warehouse / consignment stock)
 *
 * Aggregates quantities by CodiceArticolo (item code) and syncs to Offers.
 */
class InventorySyncService
{
    protected ApiClientService $api;

    public function __construct(ApiClientService $api)
    {
        $this->api = $api;
    }

    /**
     * Fetch inventory data from both endpoints and aggregate by item code.
     *
     * @param int $rows Number of rows per page
     * @return array Associative array: ['CodiceArticolo' => total_quantity]
     */
    public function fetchInventory(int $rows = 200): array
    {
        $inventory = [];

        // Fetch from internal warehouse (xbtvw_B2B_Giac)
        foreach ($this->api->paginate('xbtvw_B2B_Giac', $rows) as $pageData) {
            $list = Arr::get($pageData, 'result', []);
            foreach ($list as $row) {
                $itemCode = self::safeString($row['CodiceArticolo'] ?? null);
                $quantity = self::toFloat($row['Quantita'] ?? null);

                if ($itemCode === '' || $quantity === null) {
                    continue;
                }

                if (!isset($inventory[$itemCode])) {
                    $inventory[$itemCode] = 0;
                }
                $inventory[$itemCode] += $quantity;
            }
        }

        // Fetch from external warehouse (xbtvw_B2B_GiacCD)
        foreach ($this->api->paginate('xbtvw_B2B_GiacCD', $rows) as $pageData) {
            $list = Arr::get($pageData, 'result', []);
            foreach ($list as $row) {
                $itemCode = self::safeString($row['CodiceArticolo'] ?? null);
                $quantity = self::toFloat($row['Quantita'] ?? null);

                if ($itemCode === '' || $quantity === null) {
                    continue;
                }

                if (!isset($inventory[$itemCode])) {
                    $inventory[$itemCode] = 0;
                }
                $inventory[$itemCode] += $quantity;
            }
        }

        return $inventory;
    }

    /**
     * Sync inventory quantities to existing offers.
     * Fetches inventory data and updates offer quantities based on CodiceArticolo (external_id).
     *
     * @param int $rows Number of rows per page
     * @return array Statistics: ['updated' => int, 'skipped' => int, 'errors' => int]
     */
    public function syncInventoryToOffers(int $rows = 200): array
    {
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        // Fetch inventory data from both warehouse endpoints
        $inventory = $this->fetchInventory($rows);

        // Update offers based on inventory data
        foreach ($inventory as $itemCode => $quantity) {
            try {
                $offer = Offer::where('code', $itemCode)->first();

                if (!$offer) {
                    $skipped++;
                    continue;
                }

                $quantityInStock = (int)$quantity;
                if ((int)$offer->quantity !== $quantityInStock) {
                    $offer->quantity = $quantityInStock;
                    $offer->save();
                    $updated++;
                } else {
                    $skipped++;
                }
            } catch (\Throwable $e) {
                $errors++;
                Log::error('InventorySyncService error for item '.$itemCode.': '.$e->getMessage(), [
                    'item_code' => $itemCode,
                    'quantity' => $quantity,
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return compact('updated', 'skipped', 'errors');
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
}
