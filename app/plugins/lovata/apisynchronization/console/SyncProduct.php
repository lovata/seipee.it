<?php namespace Lovata\ApiSynchronization\console;

use Illuminate\Console\Command;
use Lovata\ApiSynchronization\classes\ProductItemsSyncService;
use Lovata\ApiSynchronization\classes\ApiClientService;

class SyncProduct extends Command
{
    protected $name = 'seipee:sync.products';

    protected $description = 'Sync products from Seipee API (xbtvw_B2B_product) into Shopaholic Products and default Offers.';

    public function handle()
    {
        $rows = (int) ($this->option('rows') ?: 200);
        $withInventory = (bool) $this->option('with-inventory');

        $api = new ApiClientService();

        try {
            $this->info('Authenticating ...');
            $api->authenticate();
        } catch (\Throwable $e) {
            $this->error('Auth failed: '.$e->getMessage());
            return 1;
        }

        $sync = new ProductItemsSyncService($api);

        if ($withInventory) {
            $this->info('Syncing products from xbtvw_B2B_product (with inventory) ...');
        } else {
            $this->info('Syncing products from xbtvw_B2B_product (without inventory) ...');
        }

        $res = $sync->sync(null, $rows, $withInventory);
        $this->line('Products: created='.$res['createdProducts'].', updated='.$res['updatedProducts'].', skipped='.$res['skippedProducts']);
        $this->line('Offers: created='.$res['createdOffers'].', updated='.$res['updatedOffers'].', skipped='.$res['skippedOffers']);
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
            ['with-inventory', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Also sync inventory quantities (slower)'],
        ];
    }
}
