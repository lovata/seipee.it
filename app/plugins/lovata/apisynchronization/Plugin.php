<?php namespace Lovata\ApiSynchronization;

use Event;
use Lovata\ApiSynchronization\Classes\Event\ExtendModelsHandler;
use Lovata\ApiSynchronization\classes\OrderExportService;
use Lovata\ApiSynchronization\console\PurgeProperties;
use Lovata\ApiSynchronization\console\SyncAll;
use Lovata\ApiSynchronization\console\SyncOrdersFromSeipee;
use Lovata\ApiSynchronization\console\SyncProduct;
use Lovata\ApiSynchronization\console\SyncProductProperties;
use Lovata\ApiSynchronization\console\SyncProperties;
use Lovata\ApiSynchronization\console\SyncProductAliases;
use Lovata\ApiSynchronization\console\SyncOrders;
use Lovata\ApiSynchronization\console\SyncUndeliveredOrders;
use Lovata\ApiSynchronization\console\SyncScheduledOrders;
use Lovata\ApiSynchronization\console\SyncUndeliveredScheduledOrders;
use Lovata\ApiSynchronization\console\SyncShippingDocuments;
use Lovata\ApiSynchronization\console\SyncUndeliveredShippingDocuments;
use Lovata\ApiSynchronization\console\ShowShippingDocumentPositions;
use Lovata\ApiSynchronization\console\ClearOrders;
use Lovata\ApiSynchronization\Models\SyncSettings;
use Lovata\OrdersShopaholic\Classes\Processor\OrderProcessor;
use \Lovata\ApiSynchronization\console\SyncCustomers;

use System\Classes\PluginBase;
use Backend;

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

    public function registerComponents()
    {
        return [
            'Lovata\ApiSynchronization\Components\ShippingDocuments' => 'shippingDocuments',
        ];
    }

    public function boot()
    {
        // Subscribe to Product and Order models extensions
        Event::subscribe(ExtendModelsHandler::class);

        Event::listen(OrderProcessor::EVENT_ORDER_CREATED, function ($order) {
            $exportService = new OrderExportService();
            $exportService->exportOrder($order);
        });
    }

    /**
     * Register settings
     */
    public function registerSettings()
    {
        return [
            'sync-settings' => [
                'label'       => 'Sync Settings',
                'description' => 'Configure automatic synchronization interval for undelivered orders',
                'category'    => 'Seipee Sync',
                'icon'        => 'icon-refresh',
                'class'       => 'Lovata\ApiSynchronization\Models\SyncSettings',
                'order'       => 500,
                'keywords'    => 'sync synchronization cron schedule interval',
                'permissions' => ['lovata.apisynchronization.access_settings'],
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'lovata.apisynchronization.access_settings' => [
                'tab'   => 'Seipee Sync',
                'label' => 'Access sync settings',
            ],
        ];
    }

    public function register()
    {
        $this->registerConsoleCommand('seipee:sync', SyncAll::class);
        $this->registerConsoleCommand('seipee:sync.properties', SyncProperties::class);
        $this->registerConsoleCommand('seipee:sync.product-properties', SyncProductProperties::class);
        $this->registerConsoleCommand('seipee:sync.products', SyncProduct::class);
        $this->registerConsoleCommand('seipee:sync.customers', SyncCustomers::class);
        $this->registerConsoleCommand('seipee:sync.product-aliases', SyncProductAliases::class);
        $this->registerConsoleCommand('seipee:sync.orders', SyncOrders::class);
        $this->registerConsoleCommand('seipee:sync.undelivered-orders', SyncUndeliveredOrders::class);
        $this->registerConsoleCommand('seipee:clear-orders', ClearOrders::class);
//        $this->registerConsoleCommand('seipee:sync.orders-from-seipee', SyncOrdersFromSeipee::class);
        $this->registerConsoleCommand('seipee:properties.purge', PurgeProperties::class);
    }

    public function registerSchedule($schedule)
    {
        // Full sync once a day
        $schedule->command('seipee:sync')->daily();

        // Sync undelivered orders with dynamic interval from settings
        try {
            $settings = SyncSettings::get('lovata_apisync_settings');

            if ($settings && $settings->is_enabled) {
                $cronExpression = $settings->getCronExpression();
                $schedule->command('seipee:sync.undelivered-orders')->cron($cronExpression);
                // Also sync undelivered scheduled orders from CFP
                $schedule->command('seipee:sync.undelivered-scheduled-orders')->cron($cronExpression);
                $schedule->command('seipee:sync.undelivered-shipping-documents')->cron($cronExpression);
            } else {
                // Fallback to default 4 hours if settings not configured
                $schedule->command('seipee:sync.undelivered-orders')->cron('0 */4 * * *');
                $schedule->command('seipee:sync.undelivered-scheduled-orders')->cron('0 */4 * * *');
                $schedule->command('seipee:sync.undelivered-shipping-documents')->cron('0 */4 * * *');
            }
        } catch (\Exception $e) {
            // Fallback to default 4 hours if settings not available (during installation)
            $schedule->command('seipee:sync.undelivered-orders')->cron('0 */4 * * *');
            $schedule->command('seipee:sync.undelivered-scheduled-orders')->cron('0 */4 * * *');
            $schedule->command('seipee:sync.undelivered-shipping-documents')->cron('0 */4 * * *');
        }
    }
}
