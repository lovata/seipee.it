<?php namespace Lovata\ApiSynchronization;

use Event;
use Lovata\ApiSynchronization\classes\OrderExportService;
use Lovata\ApiSynchronization\console\PurgeProperties;
use Lovata\ApiSynchronization\console\SyncAll;
use Lovata\ApiSynchronization\console\SyncOrdersFromSeipee;
use Lovata\ApiSynchronization\console\SyncProduct;
use Lovata\ApiSynchronization\console\SyncProductProperties;
use Lovata\ApiSynchronization\console\SyncProperties;
use Lovata\OrdersShopaholic\Classes\Processor\OrderProcessor;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public $require = [];

    public function pluginDetails()
    {
        return [
            'name'        => 'Seipee Sync',
            'description' => 'Utility commands and services to fetch data from Seipee API',
            'author'      => 'Seipee',
            'icon'        => 'icon-refresh'
        ];
    }

    public function boot()
    {
        Event::listen(OrderProcessor::EVENT_ORDER_CREATED, function ($order) {
            if (!$order) {
                return;
            }

            try {
                $exportService = new OrderExportService();
                $exportService->exportOrder($order);
            } catch (\Exception $e) {
                \Log::error('Failed to export order to Seipee: ' . $e->getMessage(), [
                    'order_id' => $order->id,
                ]);
            }
        });
    }

    public function register()
    {
        $this->registerConsoleCommand('seipee:sync', SyncAll::class);
        $this->registerConsoleCommand('seipee:sync.properties', SyncProperties::class);
        $this->registerConsoleCommand('seipee:sync.product-properties', SyncProductProperties::class);
        $this->registerConsoleCommand('seipee:sync.products', SyncProduct::class);
//        $this->registerConsoleCommand('seipee:sync.orders', SyncOrdersFromSeipee::class);
        $this->registerConsoleCommand('seipee:properties.purge', PurgeProperties::class);
    }

    public function registerSchedule($schedule)
    {
        $schedule->command('seipee:sync')->daily();
    }
}
