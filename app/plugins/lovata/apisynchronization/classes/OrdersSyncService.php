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
 * Each API record represents one order position with all order data included.
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
     * Each record contains complete order and position data.
     *
     * @param int $rows Rows per page
     * @param bool $useMock Use mock data from JSON file instead of API
     * @param string|null $mockFile Path to mock JSON file (relative to plugin root or absolute)
     * @return array Statistics
     */
    public function sync(int $rows = 200, bool $useMock = false, string $mockFile = null): array
    {
        $createdOrders = 0;
        $updatedOrders = 0;
        $createdPositions = 0;
        $updatedPositions = 0;
        $skipped = 0;
        $errors = 0;
        $processedOrders = []; // Track orders to update totals

        if ($useMock) {
            $this->log('Starting orders sync from MOCK DATA...');
            $mockData = $this->loadMockData($mockFile);
            $dataSource = [$mockData]; // Wrap in array to simulate pagination
        } else {
            $this->log('Starting orders sync from xbtvw_B2B_StoricoOrd...');
            $dataSource = $this->api->paginate('xbtvw_B2B_StoricoOrd', $rows);
        }

        foreach ($dataSource as $pageData) {
            if ($useMock) {
                $list = $pageData; // Mock data is already the list
            } else {
                $list = Arr::get($pageData, 'result', []);
            }

            if (empty($list)) {
                continue;
            }

            $this->log('Processing batch of '.count($list).' items...');

            foreach ($list as $row) {
                try {
                    $idDOTes = (int)($row['ID_DOTes'] ?? 0);
                    $idDORig = (int)($row['ID_DORig'] ?? 0);
                    $numeroDoc = $this->safeString($row['NumeroDoc'] ?? '');
                    $cdAR = $this->safeString($row['CD_AR'] ?? '');

                    // Skip invalid records
                    if (!$idDOTes || !$idDORig || !$numeroDoc || !$cdAR) {
                        $skipped++;
                        continue;
                    }

                    // Find or create Order
                    $orderResult = $this->findOrCreateOrder($row, $idDOTes);

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
     * Load mock data from JSON file
     *
     * @param string|null $mockFile Path to mock file (if null, uses default)
     * @return array Mock data
     * @throws \RuntimeException
     */
    protected function loadMockData(string $mockFile = null): array
    {
        if ($mockFile === null) {
            $mockFile = plugins_path('lovata/apisynchronization/mock_orders.json');
        } elseif (!file_exists($mockFile)) {
            // Try relative to plugin path
            $relativePath = plugins_path('lovata/apisynchronization/' . $mockFile);
            if (file_exists($relativePath)) {
                $mockFile = $relativePath;
            }
        }

        if (!file_exists($mockFile)) {
            throw new \RuntimeException('Mock data file not found: ' . $mockFile);
        }

        $this->log('Loading mock data from: ' . $mockFile);

        $content = file_get_contents($mockFile);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON in mock file: ' . json_last_error_msg());
        }

        if (!is_array($data)) {
            throw new \RuntimeException('Mock data must be an array');
        }

        $this->log('Loaded ' . count($data) . ' mock records');

        return $data;
    }

    /**
     * Sync undelivered orders and mark as delivered those that are missing from API.
     *
     * Logic:
     * 1. Fetch all undelivered orders from API (DocEvaso = false)
     * 2. Track processed seipee_order_id
     * 3. Find orders in our DB that are marked as undelivered (is_delivered = false)
     * 4. For each DB order NOT in processed list - sync individually (it became delivered)
     *
     * @param int $rows Rows per page
     * @param bool $useMock Use mock data from JSON file instead of API
     * @param string|null $mockFile Path to mock JSON file
     * @return array Statistics
     */
    public function syncUndelivered(int $rows = 200, bool $useMock = false, string $mockFile = null): array
    {
        $createdOrders = 0;
        $updatedOrders = 0;
        $createdPositions = 0;
        $updatedPositions = 0;
        $skipped = 0;
        $errors = 0;
        $processedOrders = []; // Track orders to update totals
        $processedSeipeeIds = []; // Track seipee_order_id from API

        if ($useMock) {
            $this->log('Starting undelivered orders sync from MOCK DATA...');
            $mockData = $this->loadMockData($mockFile);
            // Filter only undelivered orders from mock data
            $mockData = array_filter($mockData, function($row) {
                return ($row['DocEvaso'] ?? true) === false;
            });
            $dataSource = [$mockData]; // Wrap in array to simulate pagination
        } else {
            $this->log('Starting undelivered orders sync (DocEvaso = false)...');
            // Filter: both DVI and OCI, but only undelivered (DocEvaso = false)
            $where = "CD_DO IN ('DVI', 'OCI') AND DocEvaso = 0";
            $dataSource = $this->api->paginate('xbtvw_B2B_StoricoOrd', $rows, $where);
        }

        foreach ($dataSource as $pageData) {
            if ($useMock) {
                $list = $pageData; // Mock data is already the list
            } else {
                $list = Arr::get($pageData, 'result', []);
            }

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

                    // Track this seipee order ID
                    if ($idDOTes) {
                        $processedSeipeeIds[$idDOTes] = true;
                    }

                    if (!$idDORig || !$numeroDoc || !$cdAR) {
                        $skipped++;
                        continue;
                    }

                    // Find or create Order
                    $orderResult = $this->findOrCreateOrder($row, $idDOTes);

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

        $this->log('Undelivered orders sync completed. Checking for delivered orders...');

        // Find orders in DB marked as undelivered but not in API response
        $undeliveredInDb = Order::where('is_delivered', false)
            ->whereNotNull('seipee_order_id')
            ->pluck('seipee_order_id')
            ->toArray();

        $deliveredCount = 0;
        foreach ($undeliveredInDb as $seipeeOrderId) {
            // If not in processed list, it means the order is now delivered in API
            if (!isset($processedSeipeeIds[$seipeeOrderId])) {
                $this->log('Order '.$seipeeOrderId.' is now delivered. Syncing...');

                try {
                    $result = $this->syncOrderById($seipeeOrderId);
                    if ($result['success']) {
                        $deliveredCount++;
                        $updatedOrders++;
                    }
                } catch (\Throwable $e) {
                    $errors++;
                    $this->log('Error syncing delivered order '.$seipeeOrderId.': '.$e->getMessage(), 'error');
                    Log::error('OrdersSyncService (delivered order sync) error: '.$e->getMessage(), [
                        'seipee_order_id' => $seipeeOrderId,
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        }

        $this->log("Sync completed! Found {$deliveredCount} orders that became delivered.");
        return [
            'createdOrders' => $createdOrders,
            'updatedOrders' => $updatedOrders,
            'createdPositions' => $createdPositions,
            'updatedPositions' => $updatedPositions,
            'skipped' => $skipped,
            'errors' => $errors,
            'deliveredCount' => $deliveredCount,
        ];
    }

    /**
     * Sync a specific order by its Seipee ID.
     * Used to update orders that became delivered.
     *
     * @param int|string $seipeeOrderId Seipee order ID (ID_DOTes)
     * @return array ['success' => bool, 'message' => string]
     */
    public function syncOrderById($seipeeOrderId): array
    {
        try {
            $this->log("Fetching order {$seipeeOrderId} from API...");

            // Fetch order data from API
            $where = "ID_DOTes = {$seipeeOrderId}";
            $data = $this->api->fetch('xbtvw_B2B_StoricoOrd', 1, 100, $where);
            $list = Arr::get($data, 'result', []);

            if (empty($list)) {
                $this->log("Order {$seipeeOrderId} not found in API", 'warning');
                return ['success' => false, 'message' => 'Order not found in API'];
            }

            $updatedPositions = 0;
            $createdPositions = 0;
            $order = null;

            // Process all positions for this order
            foreach ($list as $row) {
                $idDOTes = (int)($row['ID_DOTes'] ?? 0);
                $idDORig = (int)($row['ID_DORig'] ?? 0);

                if ($idDOTes != $seipeeOrderId) {
                    continue;
                }

                // First row creates/updates the order
                if (!$order) {
                    $orderResult = $this->findOrCreateOrder($row, $idDOTes);
                    $order = $orderResult['order'];

                    if (!$order) {
                        return ['success' => false, 'message' => 'Failed to find/create order'];
                    }
                }

                // Process position
                if ($idDORig) {
                    $positionResult = $this->findOrCreateOrderPosition($order, $row, $idDORig);
                    if ($positionResult['created']) {
                        $createdPositions++;
                    } elseif ($positionResult['updated']) {
                        $updatedPositions++;
                    }
                }
            }

            // Update order totals
            if ($order) {
                $this->updateOrderTotals($order);
                $this->log("Order {$seipeeOrderId} synced successfully ({$createdPositions} created, {$updatedPositions} updated positions)");
                return ['success' => true, 'message' => 'Order synced successfully'];
            }

            return ['success' => false, 'message' => 'No order processed'];

        } catch (\Throwable $e) {
            $this->log('Error syncing order '.$seipeeOrderId.': '.$e->getMessage(), 'error');
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Find or create Order from API row.
     * Each row contains complete order data.
     *
     * @param array $row Complete order data from API
     * @param int $idDOTes Order ID from Seipee
     * @return array ['order' => Order|null, 'created' => bool, 'updated' => bool]
     */
    protected function findOrCreateOrder(array $row, int $idDOTes): array
    {
        $numeroDoc = $this->safeString($row['NumeroDoc'] ?? '');
        $cdCF = $this->safeString($row['CD_CF'] ?? ''); // Customer code
        $cdPG = $this->safeString($row['CD_PG'] ?? ''); // Payment type code
        $cdDO = $this->safeString($row['CD_DO'] ?? ''); // Document type code
        $descTipoDoc = $this->safeString($row['DescTipoDoc'] ?? ''); // Document type description

        $dataDoc = $row['DataDoc'] ?? null; // Document date
        $dataConsegna = $row['DataConsegna'] ?? null; // Delivery date
        $docEvaso = (bool)($row['DocEvaso'] ?? false); // Document fulfilled/delivered

        // Financial totals
        $notaPrincipale = $row['NotaPrincipale'] ?? null; // Main note
        $totImponibileE = $this->toFloat($row['TotImponibileE'] ?? 0); // Total excl. VAT
        $totDocumentoE = $this->toFloat($row['TotDocumentoE'] ?? 0); // Total incl. VAT
        $righe = (int)($row['Righe'] ?? 0); // Number of rows (items count)

        // Try to find existing order by seipee_order_id (most reliable)
        $order = Order::where('seipee_order_id', (string)$idDOTes)->first();

        // If not found, try by order_number
        if (!$order && $numeroDoc) {
            $order = Order::where('order_number', $numeroDoc)->first();

            // Update seipee_order_id if found by order_number
            if ($order && !$order->seipee_order_id) {
                $order->seipee_order_id = (string)$idDOTes;
                $order->save();
                $this->log('Updated seipee_order_id for existing order: '.$numeroDoc);
            }
        }

        $created = false;
        $updated = false;

        if (!$order) {
            // Create new order
            $order = new Order();
            $order->seipee_order_id = (string)$idDOTes;
            $order->order_number = $numeroDoc;

            // Try to find user by customer code
            $user = User::where('external_id', $cdCF)->first();
            if ($user) {
                $order->user_id = $user->id;
            }

            // Set default status
            $status = Status::first();
            if ($status) {
                $order->status_id = $status->id;
            }

            // Set document date as created_at
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
                } catch (\Exception $e) {
                    $this->log('Failed to parse delivery date: '.$dataConsegna, 'warning');
                }
            }

            // Set delivery status
            $order->is_delivered = $docEvaso;

            // Store additional data in property field
            $property = [];

            if ($notaPrincipale && !empty($notaPrincipale)) {
                $property['notes'] = $this->extractValue($notaPrincipale);
            }

            if ($totImponibileE !== null) {
                $property['total_excl_vat'] = $totImponibileE;
            }

            if ($totDocumentoE !== null) {
                $property['total_incl_vat'] = $totDocumentoE;
            }

            if ($cdDO) {
                $property['document_type_code'] = $cdDO;
            }

            if ($descTipoDoc) {
                $property['document_type_description'] = $descTipoDoc;
            }

            $order->property = $property;
            $order->save();
            $created = true;

            $this->log('Created order: '.$numeroDoc.' (Seipee ID: '.$idDOTes.')');
        } else {
            // Order exists, check if update needed
            $propertyChanged = false;
            $property = $order->property ?? [];

            if ($order->order_number !== $numeroDoc) {
                $order->order_number = $numeroDoc;
                $updated = true;
            }

            // Update user if not set
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
                    }
                } catch (\Exception $e) {
                    $this->log('Failed to parse delivery date: '.$dataConsegna, 'warning');
                }
            }

            // Update delivery status
            if ($order->is_delivered !== $docEvaso) {
                $order->is_delivered = $docEvaso;
                $updated = true;
            }

            // Update property fields
            if ($notaPrincipale && !empty($notaPrincipale)) {
                $newNotes = $this->extractValue($notaPrincipale);
                if (($property['notes'] ?? null) !== $newNotes) {
                    $property['notes'] = $newNotes;
                    $propertyChanged = true;
                }
            }

            if ($totImponibileE !== null && ($property['total_excl_vat'] ?? null) !== $totImponibileE) {
                $property['total_excl_vat'] = $totImponibileE;
                $propertyChanged = true;
            }

            if ($totDocumentoE !== null && ($property['total_incl_vat'] ?? null) !== $totDocumentoE) {
                $property['total_incl_vat'] = $totDocumentoE;
                $propertyChanged = true;
            }

            if ($cdDO && ($property['document_type_code'] ?? null) !== $cdDO) {
                $property['document_type_code'] = $cdDO;
                $propertyChanged = true;
            }

            if ($descTipoDoc && ($property['document_type_description'] ?? null) !== $descTipoDoc) {
                $property['document_type_description'] = $descTipoDoc;
                $propertyChanged = true;
            }

            if ($propertyChanged) {
                $order->property = $property;
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
     * Extract value from mixed type (handle empty objects/arrays)
     */
    protected function extractValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return empty($value) ? null : $value;
        }

        if (is_object($value)) {
            $arr = (array)$value;
            return empty($arr) ? null : $arr;
        }

        if (is_string($value)) {
            return trim($value) ?: null;
        }

        return $value;
    }

    /**
     * Find or create OrderPosition from API row.
     * Each row contains complete position data.
     *
     * @param Order $order
     * @param array $row Complete position data from API
     * @param int $idDORig Position ID from Seipee
     * @return array ['created' => bool, 'updated' => bool]
     */
    public function findOrCreateOrderPosition(Order $order, array $row, int $idDORig): array
    {
        $cdAR = $this->safeString($row['CD_AR'] ?? ''); // Item code
        $descrizione = $this->safeString($row['Descrizione'] ?? ''); // Description
        $variante = $this->extractVariant($row['Variante'] ?? null); // Variant
        $cdARMisura = $this->safeString($row['Cd_ARMisura'] ?? 'NR'); // Unit of measure

        $qta = $this->toFloat($row['Qta'] ?? 0); // Quantity
        $qtaEvadibile = $this->toFloat($row['QtaEvadibile'] ?? 0); // Deliverable quantity
        $prezzoUnitario = $this->toFloat($row['PrezzoUnitarioV'] ?? 0); // Unit price
        $prezzoTotale = $this->toFloat($row['PrezzoTotaleV'] ?? 0); // Total price
        $scontoRiga = $this->safeString($row['ScontoRiga'] ?? ''); // Discount

        $dataConsegna = $row['DataConsegna'] ?? null; // Delivery date
        $commessa = $row['Commessa'] ?? null; // Commission
        $sottoCommessa = $row['SottoCommessa'] ?? null; // Sub-commission
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
                // If offer not found, skip this position
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
        $property['unit_of_measure'] = $cdARMisura;
        $property['deliverable_qty'] = $qtaEvadibile;
        $property['row_fulfilled'] = $rigaEvasa;
        $property['total_price'] = $prezzoTotale;

        if ($variante) {
            $property['variant'] = $variante;
        }

        if ($scontoRiga) {
            $property['discount'] = $scontoRiga;
        }

        if ($dataConsegna) {
            $property['delivery_date'] = $dataConsegna;
        }

        if ($commessa && !empty($commessa)) {
            $property['commessa'] = $this->extractValue($commessa);
        }

        if ($sottoCommessa && !empty($sottoCommessa)) {
            $property['sotto_commessa'] = $this->extractValue($sottoCommessa);
        }

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
    /**
     * Update order totals based on positions
     *
     * @param Order $order
     * @return void
     */
    public function updateOrderTotals(Order $order): void
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

