<?php namespace Lovata\ApiSynchronization\console;

use Illuminate\Console\Command;
use Lovata\ApiSynchronization\classes\ApiClientService;
use Lovata\ApiSynchronization\classes\PropertiesSyncService;

class SyncProperties extends Command
{
    protected $name = 'seipee:sync.properties';

    protected $description = 'Sync option groups and values from Seipee API into PropertiesShopaholic (by external_id).';

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

        $sync = new PropertiesSyncService($api);

        $this->info('Syncing groups (xbtvw_B2B_TipoVar) ...');
        $resGroups = $sync->syncGroups($rows);
        $this->line('Groups: created='.$resGroups['created'].' updated='.$resGroups['updated']);

        $this->info('Syncing values (xbtvw_B2B_VarLingua) ...');
        $resValues = $sync->syncValues($rows);
        $this->line('Values: created='.$resValues['created'].' updated='.$resValues['updated'].' linked='.$resValues['linked'].' skipped='.$resValues['skipped']);

        $this->info('Syncing product properties (xbtvw_B2B_VarCf) ...');
        $resProductProps = $sync->syncProductProperties($rows);
        $this->line('Product Properties: linked='.$resProductProps['linked'].' skipped='.$resProductProps['skipped']);

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
