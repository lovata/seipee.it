<?php namespace Lovata\ApiSynchronization\Console;

use Illuminate\Console\Command;
use Lovata\ApiSynchronization\Classes\ApiClientService;
use Lovata\ApiSynchronization\Classes\ProductAliasesSyncService;
use Symfony\Component\Console\Input\InputOption;

/**
 * SyncProductAliases Command
 * Syncs product aliases (alternative codes per customer) from xbtvw_B2B_CodAlt.
 */
class SyncProductAliases extends Command
{
    protected $name = 'seipee:sync.product-aliases';

    protected $description = 'Sync product aliases from Seipee API (xbtvw_B2B_CodAlt).';

    public function handle()
    {
        $rows = (int) ($this->option('rows') ?: 200);

        $api = new ApiClientService();

        try {
            $this->info('Authenticating...');
            $api->authenticate();
        } catch (\Throwable $e) {
            $this->error('Auth failed: ' . $e->getMessage());
            return 1;
        }

        $sync = new ProductAliasesSyncService($api, $this);
        $this->info('Syncing product aliases from xbtvw_B2B_CodAlt...');

        $res = $sync->sync($rows);

        $this->line('Product Aliases: created=' . $res['created'] . ', updated=' . $res['updated'] . ', skipped=' . $res['skipped'] . ', errors=' . $res['errors']);

        $this->info('Done.');
        return 0;
    }

    public function getOptions()
    {
        return [
            ['rows', null, InputOption::VALUE_OPTIONAL, 'Rows per page', 200],
        ];
    }
}

