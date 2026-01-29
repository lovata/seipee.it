<?php namespace Lovata\ApiSynchronization\console;

use Illuminate\Console\Command;
use Lovata\ApiSynchronization\classes\ApiClientService;
use Lovata\ApiSynchronization\classes\ShippingDocumentsSyncService;
use Symfony\Component\Console\Input\InputOption;
use Log;

/**
 * SyncUndeliveredShippingDocuments Command
 */
class SyncUndeliveredShippingDocuments extends Command
{
    protected $name = 'seipee:sync.undelivered-shipping-documents';

    protected $description = 'Sync undelivered shipping documents from Seipee API (xbtvw_B2B_DVI)';

    public function handle()
    {
        $rows = $this->option('rows') ?? 200;
        $useMock = (bool) $this->option('mock');
        $mockFile = $this->option('mock-file');

        $this->info('Starting undelivered shipping documents sync...');

        try {
            $api = new ApiClientService();

            if (!$useMock) {
                $api->authenticate();
            }

            $syncService = new ShippingDocumentsSyncService($api, $this);

            if ($useMock) {
                $this->info('Syncing undelivered shipping documents from MOCK DATA...');
            } else {
                $this->info('Syncing undelivered shipping documents from Seipee API (DocEvaso = false OR RigaEvasa = false)...');
            }

            $result = $syncService->syncUndelivered($rows, $useMock, $mockFile);

            $this->info('Sync results:');
            $this->info('  Documents: created='.$result['createdDocuments'].', updated='.$result['updatedDocuments']);
            $this->info('  Positions: created='.$result['createdPositions'].', updated='.$result['updatedPositions'].', skipped='.$result['skipped']);

            if ($result['errors'] > 0) {
                $this->error('Errors: '.$result['errors']);
                return 1;
            }

            $this->info('Sync completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Error during sync: '.$e->getMessage());
            Log::error('SyncUndeliveredShippingDocuments error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }

    protected function getOptions()
    {
        return [
            ['rows', null, InputOption::VALUE_OPTIONAL, 'Number of rows per page', 200],
            ['mock', null, InputOption::VALUE_NONE, 'Use mock data from JSON file instead of API'],
            ['mock-file', null, InputOption::VALUE_OPTIONAL, 'Path to mock JSON file'],
        ];
    }
}
