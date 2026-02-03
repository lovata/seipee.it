<?php namespace Lovata\ApiSynchronization\classes;

use Illuminate\Support\Arr;
use Lovata\Shopaholic\Models\Offer;
use Log;

/**
 * InventorySyncService
 *
 * Syncs inventory data from two Seipee API endpoints:
 * - xbtvw_B2B_Giac (internal warehouse stock)
 * - xbtvw_B2B_GiacCD (external warehouse / consignment stock)
 *
 * Processes each batch immediately and updates offer quantities.
 * Stores internal and external warehouse quantities in separate fields (warehouse_internal, warehouse_external).
 * Total quantity = warehouse_internal + warehouse_external.
 */
class InventorySyncService
{
    protected ApiClientService $api;

    public function __construct(ApiClientService $api)
    {
        $this->api = $api;
    }

    /**
     * Sync inventory quantities to existing offers.
     * Processes each batch immediately and updates offers with warehouse-specific data.
     *
     * @param int $rows Number of rows per page
     * @return array Statistics: ['updated' => int, 'skipped' => int, 'errors' => int]
     */
    public function syncInventoryToOffers(int $rows = 200): array
    {
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        // Process internal warehouse (xbtvw_B2B_Giac)
        foreach ($this->api->paginate('xbtvw_B2B_Giac', $rows) as $pageData) {
            $list = Arr::get($pageData, 'result', []);
            foreach ($list as $row) {
                $itemCode = self::safeString($row['CodiceArticolo'] ?? null);
                $quantity = self::toFloat($row['Quantita'] ?? null);

                if ($itemCode === '' || $quantity === null) {
                    $skipped++;
                    continue;
                }

                try {
                    $offer = Offer::where('code', $itemCode)->first();

                    if (!$offer) {
                        $skipped++;
                        continue;
                    }

                    // Get current external warehouse quantity
                    $externalQty = (int)($offer->warehouse_external ?? 0);
                    $internalQty = (int)$quantity;

                    // Update internal warehouse quantity
                    $offer->warehouse_internal = $internalQty;

                    // Calculate total quantity
                    $totalQty = $internalQty + $externalQty;

                    if ((int)$offer->quantity !== $totalQty) {
                        $offer->quantity = $totalQty;
                        $offer->save();
                        $updated++;
                    } else {
                        $offer->save(); // Save warehouse field even if quantity didn't change
                        $skipped++;
                    }
                } catch (\Throwable $e) {
                    $errors++;
                    Log::error('InventorySyncService error (internal) for item '.$itemCode.': '.$e->getMessage(), [
                        'item_code' => $itemCode,
                        'quantity' => $quantity,
                        'warehouse' => 'internal',
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        }

        // Process external warehouse (xbtvw_B2B_GiacCD)
        foreach ($this->api->paginate('xbtvw_B2B_GiacCD', $rows) as $pageData) {
            $list = Arr::get($pageData, 'result', []);
            foreach ($list as $row) {
                $itemCode = self::safeString($row['CodiceArticolo'] ?? null);
                $quantity = self::toFloat($row['Quantita'] ?? null);

                if ($itemCode === '' || $quantity === null) {
                    $skipped++;
                    continue;
                }

                try {
                    $offer = Offer::where('code', $itemCode)->first();

                    if (!$offer) {
                        $skipped++;
                        continue;
                    }

                    // Get current internal warehouse quantity
                    $internalQty = (int)($offer->warehouse_internal ?? 0);
                    $externalQty = (int)$quantity;

                    // Update external warehouse quantity
                    $offer->warehouse_external = $externalQty;

                    // Calculate total quantity
                    $totalQty = $internalQty + $externalQty;

                    if ((int)$offer->quantity !== $totalQty) {
                        $offer->quantity = $totalQty;
                        $offer->save();
                        $updated++;
                    } else {
                        $offer->save(); // Save warehouse field even if quantity didn't change
                        $skipped++;
                    }
                } catch (\Throwable $e) {
                    $errors++;
                    Log::error('InventorySyncService error (external) for item '.$itemCode.': '.$e->getMessage(), [
                        'item_code' => $itemCode,
                        'quantity' => $quantity,
                        'warehouse' => 'external',
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
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
