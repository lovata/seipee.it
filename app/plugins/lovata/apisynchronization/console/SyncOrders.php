<?php namespace Lovata\ApiSynchronization\console;

use Illuminate\Console\Command;
use Lovata\ApiSynchronization\classes\ApiClientService;
use Lovata\ApiSynchronization\classes\OrdersSyncService;
use Symfony\Component\Console\Input\InputOption;

/**
 * SyncOrders Command
 * Syncs order items from Seipee API (xbtvw_B2B_StoricoOrd) with CD_DO = 'OCI'.
 */
class SyncOrders extends Command
{
    protected $name = 'seipee:sync.orders';

    protected $description = 'Sync order items from Seipee API (xbtvw_B2B_StoricoOrd) into OrderPosition.';

    public function handle()
    {
        $rows = (int) ($this->option('rows') ?: 200);
        $useMock = (bool) $this->option('mock');
        $mockFile = $this->option('mock-file');

        $api = new ApiClientService();

        if (!$useMock) {
            try {
                $this->info('Authenticating...');
                $api->authenticate();
            } catch (\Throwable $e) {
                $this->error('Auth failed: ' . $e->getMessage());
                return 1;
            }
        }

        $sync = new OrdersSyncService($api, $this);

        if ($useMock) {
            $this->info('Syncing orders from MOCK DATA...');
        } else {
            $this->info('Syncing orders from xbtvw_B2B_StoricoOrd...');
        }

        try {
            $res = $sync->sync($rows, $useMock, $mockFile);
        } catch (\Throwable $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            return 1;
        }

        $this->line('Orders: created=' . $res['createdOrders'] . ', updated=' . $res['updatedOrders']);
        $this->line('Positions: created=' . $res['createdPositions'] . ', updated=' . $res['updatedPositions'] . ', skipped=' . $res['skipped']);

        if ($res['errors'] > 0) {
            $this->error('Errors: ' . $res['errors']);
            return 1;
        }

        $this->info('Sync completed successfully!');
        return 0;
    }

    public function getOptions()
    {
        return [
            ['rows', null, InputOption::VALUE_OPTIONAL, 'Rows per page', 200],
            ['mock', null, InputOption::VALUE_NONE, 'Use mock data from JSON file instead of API'],
            ['mock-file', null, InputOption::VALUE_OPTIONAL, 'Path to mock JSON file (default: mock_orders.json)'],
        ];
    }
}

