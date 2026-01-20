<?php namespace Lovata\ApiSynchronization\console;

use Artisan;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class SyncAll extends Command
{
    protected $name = 'seipee:sync';

    protected $description = 'Runs Seipee sync pipeline: properties => products => product-properties => customers => product-aliases => orders.';

    public function handle()
    {
        $rows = (int)($this->option('rows') ?: 200);

        $common = [
            '--rows' => $rows,
        ];

        $this->info('Step 1/3: seipee:sync.properties');
        $code = Artisan::call('seipee:sync.properties', $common);
        $this->output->write(Artisan::output());
        if ($code !== 0) {
            $this->error('seipee:sync.properties failed with exit code '.$code);
            return $code;
        }

        $this->info('Step 2/3: seipee:sync.products');
        $code = Artisan::call('seipee:sync.products', $common);
        $this->output->write(Artisan::output());
        if ($code !== 0) {
            $this->error('seipee:sync.products failed with exit code '.$code);
            return $code;
        }

        $this->info('Step 3/3: seipee:sync.product-properties');
        $code = Artisan::call('seipee:sync.product-properties', $common);
        $this->output->write(Artisan::output());
        if ($code !== 0) {
            $this->error('seipee:sync.product-properties failed with exit code '.$code);
            return $code;
        }

        $this->info('Step 4: seipee:sync.customers');
        $code = Artisan::call('seipee:sync.customers', $common);
        $this->output->write(Artisan::output());
        if ($code !== 0) {
            $this->error('seipee:sync.customers failed with exit code '.$code);
            return $code;
        }

        $this->info('Step 5: seipee:sync.product-aliases');
        $code = Artisan::call('seipee:sync.product-aliases', $common);
        $this->output->write(Artisan::output());
        if ($code !== 0) {
            $this->error('seipee:sync.product-aliases failed with exit code '.$code);
            return $code;
        }

        $this->info('All sync steps finished successfully.');
        return 0;
    }

    public function getOptions()
    {
        return [
            ['rows', null, InputOption::VALUE_OPTIONAL, 'Rows per page', 200],
        ];
    }
}
