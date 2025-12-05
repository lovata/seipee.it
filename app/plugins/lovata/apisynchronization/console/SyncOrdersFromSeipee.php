<?php namespace Lovata\ApiSynchronization\Console;

use Illuminate\Console\Command;
use Lovata\ApiSynchronization\classes\OrderExportService;
use Lovata\OrdersShopaholic\Models\Order;

/**
 * Console command to sync order updates from Seipee API
 */
class SyncOrdersFromSeipee extends Command
{
    /**
     * @var string The console command name.
     */
    protected $signature = 'seipee:sync.orders
                            {--order_id= : Specific order ID to sync}
                            {--limit=10 : Number of recent orders to sync if no order_id specified}';

    /**
     * @var string The console command description.
     */
    protected $description = 'Sync order updates from Seipee API back to local orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = $this->option('order_id');
        $limit = (int) $this->option('limit');

        $exportService = new OrderExportService();

        if ($orderId) {
            // Sync specific order
            $order = Order::find($orderId);

            if (!$order) {
                $this->error("Order with ID {$orderId} not found.");
                return 1;
            }

            if (!$order->seipee_order_id) {
                $this->error("Order {$orderId} has no Seipee order ID. It may not have been exported yet.");
                return 1;
            }

            $this->info("Syncing order {$orderId} (Seipee ID: {$order->seipee_order_id})...");

            if ($exportService->syncOrderUpdatesFromSeipee($order)) {
                $this->info("✓ Order {$orderId} synced successfully.");
                return 0;
            } else {
                $this->error("✗ Failed to sync order {$orderId}.");
                return 1;
            }
        } else {
            // Sync recent orders with Seipee IDs
            $orders = Order::whereNotNull('seipee_order_id')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            if ($orders->isEmpty()) {
                $this->info("No orders with Seipee IDs found.");
                return 0;
            }

            $this->info("Found {$orders->count()} orders to sync.");

            $bar = $this->output->createProgressBar($orders->count());
            $bar->start();

            $successCount = 0;
            $failCount = 0;

            foreach ($orders as $order) {
                if ($exportService->syncOrderUpdatesFromSeipee($order)) {
                    $successCount++;
                } else {
                    $failCount++;
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            $this->info("Sync completed:");
            $this->info("  ✓ Success: {$successCount}");
            if ($failCount > 0) {
                $this->warn("  ✗ Failed: {$failCount}");
            }

            return 0;
        }
    }
}
