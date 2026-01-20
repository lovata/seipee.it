<?php namespace Lovata\ApiSynchronization\Console;

use Illuminate\Console\Command;
use Lovata\OrdersShopaholic\Models\Order;
use Lovata\OrdersShopaholic\Models\OrderPosition;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ClearOrders
 * Clear all orders and positions from database
 *
 * @package Lovata\ApiSynchronization\Console
 */
class ClearOrders extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'seipee:clear-orders';

    /**
     * @var string The console command description.
     */
    protected $description = 'Clear all orders and order positions from database';

    /**
     * Execute the console command.
     * @return int
     */
    public function handle()
    {
        $force = $this->option('force');

        // Confirmation
        if (!$force) {
            if (!$this->confirm('Are you sure you want to delete ALL orders and positions? This cannot be undone!')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('Starting to clear orders...');

        try {
            // Count before deletion
            $ordersCount = Order::count();
            $positionsCount = OrderPosition::count();

            $this->info("Found {$ordersCount} orders and {$positionsCount} positions");

            // Delete all order positions first
            $this->info('Deleting order positions...');
            OrderPosition::truncate();
            $this->info('✓ Order positions deleted');

            // Delete all orders
            $this->info('Deleting orders...');
            Order::truncate();
            $this->info('✓ Orders deleted');

            // Clear related tables if needed
            $this->clearRelatedTables();

            $this->info('');
            $this->info('=================================');
            $this->info('✓ Successfully cleared:');
            $this->info("  - {$ordersCount} orders");
            $this->info("  - {$positionsCount} positions");
            $this->info('=================================');

            return 0;

        } catch (\Exception $e) {
            $this->error('Error clearing orders: '.$e->getMessage());
            return 1;
        }
    }

    /**
     * Clear related tables
     */
    protected function clearRelatedTables()
    {
        try {
            // Clear order-promo mechanism relations
            \DB::table('lovata_orders_shopaholic_order_promo_mechanism')->truncate();
            $this->info('✓ Order promo mechanisms cleared');
        } catch (\Exception $e) {
            // Table might not exist
        }

        try {
            // Clear order tasks
            \DB::table('lovata_orders_shopaholic_tasks')->truncate();
            $this->info('✓ Order tasks cleared');
        } catch (\Exception $e) {
            // Table might not exist
        }

        try {
            // Clear cart positions
            \DB::table('lovata_orders_shopaholic_cart_positions')->truncate();
            $this->info('✓ Cart positions cleared');
        } catch (\Exception $e) {
            // Table might not exist
        }

        try {
            // Clear carts
            \DB::table('lovata_orders_shopaholic_carts')->truncate();
            $this->info('✓ Carts cleared');
        } catch (\Exception $e) {
            // Table might not exist
        }
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Force deletion without confirmation'],
        ];
    }
}

