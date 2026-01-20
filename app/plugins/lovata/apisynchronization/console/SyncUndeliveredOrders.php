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
            $syncResults = $this->syncUndeliveredOrdersFromSeipee($api, $rows);

            $this->info('Synced from API:');
            $this->info('  Orders: created='.$syncResults['createdOrders'].', updated='.$syncResults['updatedOrders']);
            $this->info('  Positions: created='.$syncResults['createdPositions'].', updated='.$syncResults['updatedPositions']);

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
     * Step 2: Update delivery status for local orders that are now delivered in Seipee
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

        // Fetch delivery status from API
        $deliveryStatuses = $this->fetchDeliveryStatuses($api, $seipeeOrderIds, $rows);

        // Update local orders based on API status
        foreach ($undeliveredOrders as $order) {
            $seipeeId = $order->seipee_order_id;

            if (isset($deliveryStatuses[$seipeeId])) {
                $isDeliveredInSeipee = $deliveryStatuses[$seipeeId];

                if ($isDeliveredInSeipee && !$order->is_delivered) {
                    // Order is delivered in Seipee but not in our system - update it
                    $order->is_delivered = true;
                    $order->save();
                    $markedAsDelivered++;
                    $this->info('  Marked order '.$order->order_number.' as delivered');
                } else {
                    $stillUndelivered++;
                }
            }
        }

        return [
            'markedAsDelivered' => $markedAsDelivered,
            'stillUndelivered' => $stillUndelivered,
        ];
    }

    /**
     * Fetch delivery statuses (DocEvaso) from Seipee API for specific order IDs
     *
     * @param ApiClientService $api
     * @param array $seipeeOrderIds
     * @param int $rows
     * @return array [ID_DOTes => bool (DocEvaso)]
     */
    protected function fetchDeliveryStatuses(ApiClientService $api, array $seipeeOrderIds, int $rows): array
    {
        $statuses = [];

        if (empty($seipeeOrderIds)) {
            return $statuses;
        }

        // Build WHERE clause for specific order IDs
        $idList = implode(',', array_map('intval', $seipeeOrderIds));
        $where = "ID_DOTes IN ($idList) AND CD_DO IN ('DVI', 'OCI')";

        foreach ($api->paginate('xbtvw_B2B_StoricoOrd', $rows, $where) as $pageData) {
            $list = $pageData['result'] ?? [];

            foreach ($list as $row) {
                $idDOTes = (int)($row['ID_DOTes'] ?? 0);
                $docEvaso = (bool)($row['DocEvaso'] ?? false);

                // Store the delivery status (if any row for this order is delivered, mark as delivered)
                if ($idDOTes && (!isset($statuses[$idDOTes]) || $docEvaso)) {
                    $statuses[$idDOTes] = $docEvaso;
                }
            }
        }

        return $statuses;
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

