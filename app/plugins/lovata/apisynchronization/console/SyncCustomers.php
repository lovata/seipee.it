<?php namespace Lovata\ApiSynchronization\console;

use Illuminate\Console\Command;
use Lovata\ApiSynchronization\classes\ApiClientService;
use Lovata\ApiSynchronization\classes\CustomersSyncService;

class SyncCustomers extends Command
{
    protected $name = 'seipee:sync.customers';

    protected $description = 'Sync customers from Seipee API (xbtvw_B2B_CUSTOMERS) and create RainLab users with random passwords.';

    public function handle()
    {
        $rows = (int) ($this->option('rows') ?: 7);

        $api = new ApiClientService();

        try {
            $this->info('Authenticating ...');
            $api->authenticate();
        } catch (\Throwable $e) {
            $this->error('Auth failed: '.$e->getMessage());
            return 1;
        }

        $sync = new CustomersSyncService($api, $this);
        $this->info('Syncing customers from xbtvw_B2B_CUSTOMERS ...');

        $res = $sync->sync($rows);

        $this->line('Customers: created='.$res['created'].', updated='.$res['updated'].', skipped='.$res['skipped']);
        if (!empty($res['passwords'])) {
            $this->info('Generated passwords for newly created users:');
            foreach ($res['passwords'] as $email => $pass) {
                $this->line($email.': '.$pass);
            }
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
