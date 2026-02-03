<?php namespace Lovata\ApiSynchronization\console;

use Illuminate\Console\Command;
use Lovata\ApiSynchronization\classes\InventorySyncService;
use Lovata\ApiSynchronization\classes\ApiClientService;

/**
 * SyncInventory Console Command
 *
 * Syncs inventory quantities from Seipee API to existing Offers.
 * Only updates quantities, does not create products or offers.
 */
class SyncInventory extends Command
{
    protected $name = 'seipee:sync.inventory';

    protected $description = 'Sync inventory quantities from Seipee API (xbtvw_B2B_Giac and xbtvw_B2B_GiacCD) to existing Offers.';

    public function handle()
    {
        $rows = (int) ($this->option('rows') ?: 200);

        $api = new ApiClientService();

        try {
            $this->info('Authenticating ...');
            $api->authenticate();
        } catch (\Throwable $e) {
            $this->error('Auth failed: '.$e->getMessage());
            return 1;
        }

        $sync = new InventorySyncService($api);
        $this->info('Syncing inventory from xbtvw_B2B_Giac and xbtvw_B2B_GiacCD ...');

        $res = $sync->syncInventoryToOffers($rows);
        $this->line('Offers updated: '.$res['updated'].', skipped: '.$res['skipped']);

        if (!empty($res['errors'])) {
            $this->warn('Errors: '.$res['errors']);
        }

        $this->info('Done.');
        return 0;
    }

    public function getOptions()
    {
        return [
            ['rows', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'Rows per page', 200],
        ];
    }
}
