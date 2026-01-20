<?php namespace Lovata\ApiSynchronization\console;

use Illuminate\Console\Command;
use Lovata\ApiSynchronization\classes\ApiClientService;
use Lovata\ApiSynchronization\classes\OrdersSyncService;
use Lovata\OrdersShopaholic\Models\Order;
use Symfony\Component\Console\Input\InputOption;
use Log;

/**
 * Class SyncUndeliveredOrders
 * Syncs only undelivered orders from Seipee API and updates delivery status
 *
 * @package Lovata\ApiSynchronization\Console
 */
class SyncUndeliveredOrders extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'seipee:sync.undelivered-orders';

    /**
     * @var string The console command description.
     */
    protected $description = 'Sync undelivered orders from Seipee API and update delivery status (runs every 4 hours)';

    /**
     * Execute the console command.
     * @return int
     */
    public function handle()
    {
        $rows = $this->option('rows') ?? 200;

        $this->info('Starting undelivered orders sync...');

        try {
            $api = new ApiClientService();
            $api->authenticate();

            // Step 1: Sync undelivered orders from Seipee
            $this->info('Step 1: Syncing undelivered orders from Seipee API (DocEvaso = false)...');
//            $syncResults = $this->syncUndeliveredOrdersFromSeipee($api, $rows);
//
//            $this->info('Synced from API:');
//            $this->info('  Orders: created='.$syncResults['createdOrders'].', updated='.$syncResults['updatedOrders']);
//            $this->info('  Positions: created='.$syncResults['createdPositions'].', updated='.$syncResults['updatedPositions']);

            // Step 2: Update delivery status for orders that became delivered
            $this->info('Step 2: Checking delivery status for local undelivered orders...');
            $updateResults = $this->updateDeliveryStatusFromSeipee($api, $rows);

            $this->info('Updated delivery status:');
            $this->info('  Orders marked as delivered: '.$updateResults['markedAsDelivered']);
            $this->info('  Orders still undelivered: '.$updateResults['stillUndelivered']);

            $this->info('Sync completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Error during sync: '.$e->getMessage());
            Log::error('SyncUndeliveredOrders error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }

    /**
     * Step 1: Sync undelivered orders from Seipee (DocEvaso = false)
     *
     * @param ApiClientService $api
     * @param int $rows
     * @return array
     */
    protected function syncUndeliveredOrdersFromSeipee(ApiClientService $api, int $rows): array
    {
        // Use OrdersSyncService but filter by DocEvaso = false (undelivered)
        $syncService = new OrdersSyncService($api, $this);

        // Sync with filter for undelivered orders only
        return $syncService->syncUndelivered($rows);
    }

    /**
     * Step 2: Full sync/update for local undelivered orders
     * Updates all order data and positions (add/update/delete) from Seipee API
     *
     * @param ApiClientService $api
     * @param int $rows
     * @return array
     */
    protected function updateDeliveryStatusFromSeipee(ApiClientService $api, int $rows): array
    {
        $markedAsDelivered = 0;
        $stillUndelivered = 0;

        // Get all local orders that are NOT delivered
        $undeliveredOrders = Order::where('is_delivered', false)
            ->whereNotNull('seipee_order_id')
            ->get();

        $this->info('Found '.$undeliveredOrders->count().' undelivered orders in local system');

        if ($undeliveredOrders->isEmpty()) {
            return [
                'markedAsDelivered' => 0,
                'stillUndelivered' => 0,
            ];
        }

        // Get their IDs to check in API
        $seipeeOrderIds = $undeliveredOrders->pluck('seipee_order_id')->toArray();

        // Full sync orders from API
        $this->fullSyncOrders($api, $seipeeOrderIds, $rows);

        // Count updated delivery statuses
        foreach ($undeliveredOrders as $order) {
            // Reload order from DB to get fresh data
            $order->refresh();

            if ($order->is_delivered) {
                $markedAsDelivered++;
                $this->info('  Order '.$order->order_number.' is now delivered');
            } else {
                $stillUndelivered++;
            }
        }

        return [
            'markedAsDelivered' => $markedAsDelivered,
            'stillUndelivered' => $stillUndelivered,
        ];
    }

    /**
     * Full sync orders from Seipee API - update all order data and positions
     *
     * @param ApiClientService $api
     * @param array $seipeeOrderIds
     * @param int $rows
     * @return void
     */
    protected function fullSyncOrders(ApiClientService $api, array $seipeeOrderIds, int $rows): void
    {
        if (empty($seipeeOrderIds)) {
            return;
        }

        // Use OrdersSyncService for full sync
        $syncService = new OrdersSyncService($api, $this);

        // Build WHERE clause for specific order IDs
        $idList = implode(',', array_map('intval', $seipeeOrderIds));
        $where = "ID_DOTes IN ($idList) AND CD_DO IN ('DVI', 'OCI')";

        $this->info('Fetching latest data from API for '.count($seipeeOrderIds).' orders...');

        // Collect all API data for these orders
        $orderData = []; // [ID_DOTes => [rows]]
        $deliveryDates = [];

        foreach ($api->paginate('xbtvw_B2B_StoricoOrd', $rows, $where) as $pageData) {
            $list = $pageData['result'] ?? [];

            foreach ($list as $row) {
                $idDOTes = (int)($row['ID_DOTes'] ?? 0);
                $cdDO = $row['CD_DO'] ?? '';

                if (!$idDOTes) {
                    continue;
                }

                // Collect delivery dates from DVI
                if ($cdDO === 'DVI') {
                    $dataConsegna = $row['DataConsegna'] ?? null;
                    if ($dataConsegna && !isset($deliveryDates[$idDOTes])) {
                        $deliveryDates[$idDOTes] = $dataConsegna;
                    }
                }

                // Group rows by order ID
                if (!isset($orderData[$idDOTes])) {
                    $orderData[$idDOTes] = [];
                }
                $orderData[$idDOTes][] = $row;
            }
        }

        $this->info('Processing '.count($orderData).' orders from API...');

        // Process each order
        foreach ($orderData as $idDOTes => $rows) {
            try {
                $this->syncSingleOrder($syncService, $idDOTes, $rows, $deliveryDates);
            } catch (\Exception $e) {
                $this->error('Error syncing order ID '.$idDOTes.': '.$e->getMessage());
                Log::error('Full sync error for order: '.$e->getMessage(), [
                    'idDOTes' => $idDOTes,
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * Sync single order with all its positions
     *
     * @param OrdersSyncService $syncService
     * @param int $idDOTes
     * @param array $rows
     * @param array $deliveryDates
     * @return void
     */
    protected function syncSingleOrder(OrdersSyncService $syncService, int $idDOTes, array $rows, array $deliveryDates): void
    {
        // Find local order
        $order = Order::where('seipee_order_id', (string)$idDOTes)->first();

        if (!$order) {
            $this->warn('  Order with Seipee ID '.$idDOTes.' not found locally, skipping');
            return;
        }

        // Track existing position IDs from API
        $apiPositionIds = [];

        // Process each row (DVI and OCI)
        foreach ($rows as $row) {
            $idDORig = (int)($row['ID_DORig'] ?? 0);
            $cdDO = $row['CD_DO'] ?? '';

            if (!$idDORig || ($cdDO !== 'DVI' && $cdDO !== 'OCI')) {
                continue;
            }

            // Track this position
            $apiPositionIds[] = $idDORig;

            // Update order data (from any row, will be same for all rows of this order)
            $this->updateOrderFromRow($order, $row, $deliveryDates);

            // Create/update position
            $syncService->findOrCreateOrderPosition($order, $row, $idDORig);
        }

        // Save order
        $order->save();

        // Delete positions that no longer exist in API
        $this->deleteRemovedPositions($order, $apiPositionIds);

        // Update order totals
        $syncService->updateOrderTotals($order);

        $this->info('  Synced order '.$order->order_number.' with '.count($apiPositionIds).' positions');
    }

    /**
     * Update order fields from API row
     *
     * @param Order $order
     * @param array $row
     * @param array $deliveryDates
     * @return void
     */
    protected function updateOrderFromRow(Order $order, array $row, array $deliveryDates): void
    {
        $idDOTes = (int)($row['ID_DOTes'] ?? 0);
        $cdPG = $row['CD_PG'] ?? null;
        $docEvaso = (bool)($row['DocEvaso'] ?? false);

        // Update payment type
        if ($cdPG && $order->payment_type !== $cdPG) {
            $order->payment_type = $cdPG;
        }

        // Update delivery date from collected dates
        if (isset($deliveryDates[$idDOTes])) {
            try {
                $newDeliveryDate = \Carbon\Carbon::parse($deliveryDates[$idDOTes]);
                if (!$order->delivery_date || !$order->delivery_date->eq($newDeliveryDate)) {
                    $order->delivery_date = $newDeliveryDate;
                }
            } catch (\Exception $e) {
                // Ignore parsing errors
            }
        }

        // Update delivery status
        if ($order->is_delivered !== $docEvaso) {
            $order->is_delivered = $docEvaso;
        }
    }

    /**
     * Delete positions that no longer exist in API
     *
     * @param Order $order
     * @param array $apiPositionIds Array of ID_DORig from API
     * @return void
     */
    protected function deleteRemovedPositions(Order $order, array $apiPositionIds): void
    {
        if (empty($apiPositionIds)) {
            return;
        }

        // Find positions that exist locally but not in API
        $deletedCount = $order->order_position()
            ->whereRaw("JSON_EXTRACT(property, '$.seipee_row_id') IS NOT NULL")
            ->whereRaw("JSON_EXTRACT(property, '$.seipee_row_id') NOT IN (".implode(',', $apiPositionIds).")")
            ->delete();

        if ($deletedCount > 0) {
            $this->info('    Deleted '.$deletedCount.' removed positions from order '.$order->order_number);
        }
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['rows', null, InputOption::VALUE_OPTIONAL, 'Number of rows per page', 200],
        ];
    }
}

