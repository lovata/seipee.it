<?php namespace Lovata\ApiSynchronization\console;

use Illuminate\Console\Command;
use Lovata\ApiSynchronization\classes\ProductPropertiesSyncService;
use Lovata\ApiSynchronization\classes\ApiClientService;

class SyncProductProperties extends Command
{
    protected $name = 'seipee:sync.product-properties';

    protected $description = 'Attach product property values from Seipee API (xbtvw_B2B_productVar) to Shopaholic products by external_id.';

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

        $sync = new ProductPropertiesSyncService($api);
        $this->info('Syncing product properties ...');
        $res = $sync->sync(null, $rows);
        $this->line('Products processed='.$res['productsProcessed'].', links created='.$res['linksCreated'].', links existing='.$res['linksUpdated'].', skipped='.$res['skipped'].', missing refs='.$res['missing']);

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
