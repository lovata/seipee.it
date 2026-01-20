<?php namespace Lovata\ApiSynchronization\classes;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Log;
use Lovata\OrdersShopaholic\Models\Order;
use Lovata\OrdersShopaholic\Models\OrderPosition;
use Lovata\OrdersShopaholic\Models\Status;
use Lovata\Shopaholic\Models\Offer;
use Lovata\Shopaholic\Models\Product;
use Lovata\Buddies\Models\User;

/**
 * OrdersSyncService
 *
 * Syncs order history from Seipee API (xbtvw_B2B_StoricoOrd) into OrderPosition.
 */
class OrdersSyncService
{
    /** @var ApiClientService */
    protected ApiClientService $api;

    /** @var Command|null */
    protected ?Command $console;

    public function __construct(ApiClientService $api, Command $console = null)
    {
        $this->api = $api;
        $this->console = $console;
    }

    /**
     * Sync orders from Seipee API table xbtvw_B2B_StoricoOrd.
     *
     * @param int $rows Rows per page
     * @return array Statistics
     */
    public function sync(int $rows = 200): array
    {
        $createdOrders = 0;
        $updatedOrders = 0;
        $createdPositions = 0;
        $updatedPositions = 0;
        $skipped = 0;
        $errors = 0;
        $processedOrders = []; // Track orders to update totals
        $deliveryDates = []; // Store delivery dates from DVI records

        $this->log('Starting orders sync from xbtvw_B2B_StoricoOrd...');

        // Single request for both DVI and OCI records
        $where = "CD_DO IN ('DVI', 'OCI')";
        $where = "";

        foreach ($this->api->paginate('xbtvw_B2B_StoricoOrd', $rows, $where) as $pageData) {
            $list = Arr::get($pageData, 'result', []);

            if (empty($list)) {
                continue;
            }

            $this->log('Processing batch of '.count($list).' items...');

            foreach ($list as $row) {
                try {
                    $cdDO = $this->safeString($row['CD_DO'] ?? '');
                    $idDOTes = (int)($row['ID_DOTes'] ?? 0);
                    $idDORig = (int)($row['ID_DORig'] ?? 0);
                    $numeroDoc = $this->safeString($row['NumeroDoc'] ?? '');
                    $cdAR = $this->safeString($row['CD_AR'] ?? '');

                    // Collect delivery dates from DVI records
                    if ($cdDO === 'DVI') {
                        $dataConsegna = $row['DataConsegna'] ?? null;
                        if ($idDOTes && $dataConsegna && !isset($deliveryDates[$idDOTes])) {
                            $deliveryDates[$idDOTes] = $dataConsegna;
                        }
                    }

                    // Process both DVI and OCI records: create orders and positions
                    if ($cdDO === 'DVI' || $cdDO === 'OCI') {
                        if (!$idDORig || !$numeroDoc || !$cdAR) {
                            $skipped++;
                            continue;
                        }

                        // Find or create Order
                        $orderResult = $this->findOrCreateOrder($row, $idDOTes, $deliveryDates);

                        // Free memory: remove used delivery date immediately
                        if (isset($deliveryDates[$idDOTes])) {
                            unset($deliveryDates[$idDOTes]);
                        }

                        if (!$orderResult['order']) {
                            $skipped++;
                            continue;
                        }

                        $order = $orderResult['order'];
                        if ($orderResult['created']) {
                            $createdOrders++;
                        } elseif ($orderResult['updated']) {
                            $updatedOrders++;
                        }

                        // Track order for totals update
                        $processedOrders[$order->id] = $order;

                        // Find or create OrderPosition
                        $positionResult = $this->findOrCreateOrderPosition($order, $row, $idDORig);
                        if ($positionResult['created']) {
                            $createdPositions++;
                        } elseif ($positionResult['updated']) {
                            $updatedPositions++;
                        } else {
                            $skipped++;
                        }
                    }

                } catch (\Throwable $e) {
                    $errors++;
                    $this->log('Error processing row: '.$e->getMessage(), 'error');
                    Log::error('OrdersSyncService error: '.$e->getMessage(), [
                        'row' => $row ?? null,
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            // Update order totals for all orders in this batch
            foreach ($processedOrders as $order) {
                $this->updateOrderTotals($order);
            }
            $processedOrders = [];
        }

        $this->log('Sync completed!');
        return [
            'createdOrders' => $createdOrders,
            'updatedOrders' => $updatedOrders,
            'createdPositions' => $createdPositions,
            'updatedPositions' => $updatedPositions,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Sync only undelivered orders (DocEvaso = false)
     * Used for periodic updates to check delivery status
     *
     * @param int $rows Rows per page
     * @return array Statistics
     */
    public function syncUndelivered(int $rows = 200): array
    {
        $createdOrders = 0;
        $updatedOrders = 0;
        $createdPositions = 0;
        $updatedPositions = 0;
        $skipped = 0;
        $errors = 0;
        $processedOrders = []; // Track orders to update totals
        $deliveryDates = []; // Store delivery dates from DVI records

        $this->log('Starting undelivered orders sync (DocEvaso = false)...');

        // Filter: both DVI and OCI, but only undelivered (DocEvaso = false)
        $where = "CD_DO IN ('DVI', 'OCI') AND DocEvaso = 0";

        foreach ($this->api->paginate('xbtvw_B2B_StoricoOrd', $rows, $where) as $pageData) {
            $list = Arr::get($pageData, 'result', []);

            if (empty($list)) {
                continue;
            }

            $this->log('Processing batch of '.count($list).' undelivered items...');

            foreach ($list as $row) {
                try {
                    $cdDO = $this->safeString($row['CD_DO'] ?? '');
                    $idDOTes = (int)($row['ID_DOTes'] ?? 0);
                    $idDORig = (int)($row['ID_DORig'] ?? 0);
                    $numeroDoc = $this->safeString($row['NumeroDoc'] ?? '');
                    $cdAR = $this->safeString($row['CD_AR'] ?? '');

                    // Collect delivery dates from DVI records
                    if ($cdDO === 'DVI') {
                        $dataConsegna = $row['DataConsegna'] ?? null;
                        if ($idDOTes && $dataConsegna && !isset($deliveryDates[$idDOTes])) {
                            $deliveryDates[$idDOTes] = $dataConsegna;
                        }
                    }

                    // Process both DVI and OCI records
                    if ($cdDO === 'DVI' || $cdDO === 'OCI') {
                        if (!$idDORig || !$numeroDoc || !$cdAR) {
                            $skipped++;
                            continue;
                        }

                        // Find or create Order
                        $orderResult = $this->findOrCreateOrder($row, $idDOTes, $deliveryDates);

                        // Free memory: remove used delivery date immediately
                        if (isset($deliveryDates[$idDOTes])) {
                            unset($deliveryDates[$idDOTes]);
                        }

                        if (!$orderResult['order']) {
                            $skipped++;
                            continue;
                        }

                        $order = $orderResult['order'];
                        if ($orderResult['created']) {
                            $createdOrders++;
                        } elseif ($orderResult['updated']) {
                            $updatedOrders++;
                        }

                        // Track order for totals update
                        $processedOrders[$order->id] = $order;

                        // Find or create OrderPosition
                        $positionResult = $this->findOrCreateOrderPosition($order, $row, $idDORig);
                        if ($positionResult['created']) {
                            $createdPositions++;
                        } elseif ($positionResult['updated']) {
                            $updatedPositions++;
                        } else {
                            $skipped++;
                        }
                    }

                } catch (\Throwable $e) {
                    $errors++;
                    $this->log('Error processing row: '.$e->getMessage(), 'error');
                    Log::error('OrdersSyncService (undelivered) error: '.$e->getMessage(), [
                        'row' => $row ?? null,
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            // Update order totals for all orders in this batch
            foreach ($processedOrders as $order) {
                $this->updateOrderTotals($order);
            }
            $processedOrders = [];
        }

        $this->log('Undelivered orders sync completed!');
        return [
            'createdOrders' => $createdOrders,
            'updatedOrders' => $updatedOrders,
            'createdPositions' => $createdPositions,
            'updatedPositions' => $updatedPositions,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Find or create Order from API row
     *
     * @param array $row
     * @param int $idDOTes
     * @param array $deliveryDates Array of delivery dates from DVI records
     * @return array ['order' => Order|null, 'created' => bool, 'updated' => bool]
     */
    protected function findOrCreateOrder(array $row, int $idDOTes, array $deliveryDates = []): array
    {
        $numeroDoc = $this->safeString($row['NumeroDoc'] ?? '');
        $cdCF = $this->safeString($row['CD_CF'] ?? ''); // Customer code
        $dataDoc = $row['DataDoc'] ?? null; // Document date
        $cdPG = $this->safeString($row['CD_PG'] ?? ''); // Payment type code
        $docEvaso = (bool)($row['DocEvaso'] ?? false); // Document fulfilled/delivered

        // Get delivery date from DVI records (not from OCI row)
        $dataConsegna = $deliveryDates[$idDOTes] ?? null;

        // Try to find existing order by seipee_order_id (ID_DOTes)
        $order = Order::where('seipee_order_id', (string)$idDOTes)->first();

        $created = false;
        $updated = false;

        if (!$order) {
            // Create new order
            $order = new Order();
            $order->seipee_order_id = (string)$idDOTes;
            $order->order_number = $numeroDoc;

            // Try to find user by customer code (stored in external_id)
            $user = User::where('external_id', $cdCF)->first();
            if ($user) {
                $order->user_id = $user->id;
            }

            // Set default status (find first or create)
            $status = Status::first();
            if ($status) {
                $order->status_id = $status->id;
            }

            // Parse and set document date
            if ($dataDoc) {
                try {
                    $order->created_at = \Carbon\Carbon::parse($dataDoc);
                } catch (\Exception $e) {
                    // Use current time if parsing fails
                }
            }

            // Set payment type
            if ($cdPG) {
                $order->payment_type = $cdPG;
            }

            // Set delivery date
            if ($dataConsegna) {
                try {
                    $order->delivery_date = \Carbon\Carbon::parse($dataConsegna);
                    $this->log('Set delivery_date: '.$dataConsegna.' for order '.$numeroDoc);
                } catch (\Exception $e) {
                    $this->log('Failed to parse delivery date: '.$dataConsegna.' - '.$e->getMessage(), 'warning');
                }
            } else {
                $this->log('No delivery date found for order '.$numeroDoc.' (ID_DOTes: '.$idDOTes.')');
            }

            // Set delivery status
            $order->is_delivered = $docEvaso;

            $order->save();
            $created = true;

            $this->log('Created order: '.$numeroDoc.' (Seipee ID: '.$idDOTes.')');
        } else {
            // Order exists, check if update needed
            if ($order->order_number !== $numeroDoc) {
                $order->order_number = $numeroDoc;
                $updated = true;
            }

            // Update user if found and not set
            if (!$order->user_id && $cdCF) {
                $user = User::where('external_id', $cdCF)->first();
                if ($user) {
                    $order->user_id = $user->id;
                    $updated = true;
                }
            }

            // Update payment type
            if ($cdPG && $order->payment_type !== $cdPG) {
                $order->payment_type = $cdPG;
                $updated = true;
            }

            // Update delivery date
            if ($dataConsegna) {
                try {
                    $newDeliveryDate = \Carbon\Carbon::parse($dataConsegna);
                    if (!$order->delivery_date || $order->delivery_date->ne($newDeliveryDate)) {
                        $order->delivery_date = $newDeliveryDate;
                        $updated = true;
                        $this->log('Updated delivery_date: '.$dataConsegna.' for order '.$numeroDoc);
                    }
                } catch (\Exception $e) {
                    $this->log('Failed to parse delivery date: '.$dataConsegna.' - '.$e->getMessage(), 'warning');
                }
            }

            // Update delivery status
            if ($order->is_delivered !== $docEvaso) {
                $order->is_delivered = $docEvaso;
                $updated = true;
            }

            if ($updated) {
                $order->save();
                $this->log('Updated order: '.$numeroDoc);
            }
        }

        return [
            'order' => $order,
            'created' => $created,
            'updated' => $updated,
        ];
    }

    /**
     * Find or create OrderPosition from API row
     *
     * @param Order $order
     * @param array $row
     * @param int $idDORig
     * @return array ['created' => bool, 'updated' => bool]
     */
    protected function findOrCreateOrderPosition(Order $order, array $row, int $idDORig): array
    {
        $cdAR = $this->safeString($row['CD_AR'] ?? ''); // Item code
        $descrizione = $this->safeString($row['Descrizione'] ?? '');
        $variante = $this->extractVariant($row['Variante'] ?? null);
        $cdARMisura = $this->safeString($row['Cd_ARMisura'] ?? 'NR'); // Unit of measure
        $qta = $this->toFloat($row['Qta'] ?? 0);

        // Debug: check raw value from API

        $qtaEvadibile = $this->toFloat($row['QtaEvadibile'] ?? 0); // Deliverable quantity
        $prezzoUnitario = $this->toFloat($row['PrezzoUnitarioV'] ?? 0);
        $prezzoTotale = $this->toFloat($row['PrezzoTotaleV'] ?? 0);
        $scontoRiga = $this->safeString($row['ScontoRiga'] ?? '');
        $dataConsegna = $row['DataConsegna'] ?? null;
        $rigaEvasa = (bool)($row['RigaEvasa'] ?? false); // Row fulfilled flag

        // Find offer by code
        $offer = Offer::where('code', $cdAR)->first();

        // Try to find existing position by order_id and external row ID stored in property
        $position = OrderPosition::where('order_id', $order->id)
            ->whereRaw("JSON_EXTRACT(property, '$.seipee_row_id') = ?", [$idDORig])
            ->first();

        $created = false;
        $updated = false;

        if (!$position) {
            // Create new position
            $position = new OrderPosition();
            $position->order_id = $order->id;

            if ($offer) {
                $position->item_id = $offer->id;
                $position->item_type = Offer::class;
                $position->offer_id = $offer->id;
            } else {
                // If offer not found, we need to create a generic position
                // Use a dummy product or skip - for now let's skip
                $this->log('Offer not found for code: '.$cdAR, 'warning');
                return ['created' => false, 'updated' => false];
            }

            $created = true;
        } else {
            $updated = true;
        }

        // Update position fields
        $position->quantity = (int)$qta;
        $position->price = $prezzoUnitario;
        $position->code = $cdAR;

        // Store additional data in property field
        $property = $position->property ?? [];
        $property['seipee_row_id'] = $idDORig;
        $property['description'] = $descrizione;
        $property['deliverable_qty'] = $qtaEvadibile; // For delivery status calculation
        $property['row_fulfilled'] = $rigaEvasa; // Row fulfillment flag
        if ($variante) {
            $property['variant'] = $variante;
        }
        if ($scontoRiga) {
            $property['discount'] = $scontoRiga;
        }
        if ($dataConsegna) {
            $property['delivery_date'] = $dataConsegna;
        }
        $property['total_price'] = $prezzoTotale;

        $position->property = $property;

        $position->save();

        if ($created) {
            $this->log('Created position: '.$cdAR.' for order '.$order->order_number);
        } elseif ($updated) {
            $this->log('Updated position: '.$cdAR.' for order '.$order->order_number);
        }

        return [
            'created' => $created,
            'updated' => $updated,
        ];
    }

    /**
     * Extract variant information from the Variante field
     */
    protected function extractVariant($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            return trim($value);
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        return (string)$value;
    }

    /**
     * Convert value to float
     */
    protected function toFloat($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_array($value) || is_object($value)) {
            return null;
        }
        if (is_numeric($value)) {
            return (float)$value;
        }
        $v = str_replace([' ', ','], ['', '.'], (string)$value);
        return is_numeric($v) ? (float)$v : null;
    }

    /**
     * Safe string extraction
     */
    protected function safeString($value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_array($value) || is_object($value)) {
            return '';
        }
        return trim((string)$value);
    }

    /**
     * Update order totals: items_count
     * Note: position_total_price is a computed field and should not be set directly
     *
     * @param Order $order
     */
    protected function updateOrderTotals(Order $order): void
    {
        // Reload positions to get latest data
        $order->load('order_position');

        // Calculate total items count (sum of quantities)
        $itemsCount = 0;

        foreach ($order->order_position as $position) {
            $itemsCount += (int)$position->quantity;
        }

        // Update order if value changed
        if ($order->items_count !== $itemsCount) {
            $order->items_count = $itemsCount;
            $order->save();
            $this->log('Updated items count for order '.$order->order_number.': '.$itemsCount.' items');
        }
    }

    /**
     * Log message to console if available
     */
    protected function log(string $message, string $level = 'info'): void
    {
        if ($this->console) {
            if ($level === 'error') {
                $this->console->error($message);
            } elseif ($level === 'warning') {
                $this->console->warn($message);
            } else {
                $this->console->info($message);
            }
        }
    }
}

