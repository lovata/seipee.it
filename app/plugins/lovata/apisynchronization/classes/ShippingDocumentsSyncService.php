<?php namespace Lovata\ApiSynchronization\classes;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Log;
use Lovata\ApiSynchronization\Models\ShippingDocument;
use Lovata\ApiSynchronization\Models\ShippingDocumentPosition;
use Lovata\OrdersShopaholic\Models\OrderPosition;
use Lovata\Shopaholic\Models\Offer;
use Lovata\Buddies\Models\User;

/**
 * ShippingDocumentsSyncService
 *
 * Syncs shipping documents from Seipee API (xbtvw_B2B_DVI) into ShippingDocument.
 */
class ShippingDocumentsSyncService
{
    protected ApiClientService $api;
    protected ?Command $console;

    public function __construct(ApiClientService $api, Command $console = null)
    {
        $this->api = $api;
        $this->console = $console;
    }

    /**
     * Sync shipping documents from Seipee API
     *
     * @param int $rows Rows per page
     * @param bool $useMock Use mock data
     * @param string|null $mockFile Mock file path
     * @return array Statistics
     */
    public function sync(int $rows = 200, bool $useMock = false, string $mockFile = null): array
    {
        $createdDocuments = 0;
        $updatedDocuments = 0;
        $createdPositions = 0;
        $updatedPositions = 0;
        $skipped = 0;
        $errors = 0;

        if ($useMock) {
            $this->log('Starting shipping documents sync from MOCK DATA...');
            $mockData = $this->loadMockData($mockFile);
            $dataSource = [$mockData];
        } else {
            $this->log('Starting shipping documents sync from xbtvw_B2B_StoricoDDT...');
            $dataSource = $this->api->paginate('xbtvw_B2B_StoricoDDT', $rows);
        }

        try {
            foreach ($dataSource as $pageData) {
                try {
                    if ($useMock) {
                        $list = $pageData;
                    } else {
                        $list = Arr::get($pageData, 'result', []);
                    }

                    if (empty($list)) {
                        continue;
                    }

                    $this->log('Processing batch of '.count($list).' items...');

                    foreach ($list as $row) {
                        try {
                            $idDOTes = (int)($row['ID_DOTes'] ?? 0);
                            $idDORig = (int)($row['ID_DORig'] ?? 0);
                            $numeroDoc = $this->safeString($row['NumeroDoc'] ?? '');
                            $cdAR = $this->safeString($row['CD_AR'] ?? '');

                            if (!$idDOTes || !$idDORig || !$numeroDoc || !$cdAR) {
                                $skipped++;
                                continue;
                            }

                            $documentResult = $this->findOrCreateDocument($row, $idDOTes);

                            if (!$documentResult['document']) {
                                $skipped++;
                                continue;
                            }

                            $document = $documentResult['document'];
                            if ($documentResult['created']) {
                                $createdDocuments++;
                            } elseif ($documentResult['updated']) {
                                $updatedDocuments++;
                            }

                            $positionResult = $this->findOrCreatePosition($document, $row, $idDORig);
                            if ($positionResult['created']) {
                                $createdPositions++;
                            } elseif ($positionResult['updated']) {
                                $updatedPositions++;
                            } else {
                                $skipped++;
                            }
                        } catch (\Throwable $e) {
                            $errors++;
                            $this->log('Error processing row: '.$e->getMessage(), 'error');
                            Log::error('ShippingDocumentsSyncService error: '.$e->getMessage(), [
                                'row' => $row ?? null,
                                'trace' => $e->getTraceAsString(),
                            ]);
                        }
                    }
                } catch (\RuntimeException $e) {
                    if (strpos($e->getMessage(), 'timeout') !== false) {
                        $this->log('Timeout error on page: '.$e->getMessage(), 'warning');
                        break;
                    }
                    throw $e;
                }
            }
        } catch (\Throwable $e) {
            $this->log('Critical error during sync: '.$e->getMessage(), 'error');
            Log::error('ShippingDocumentsSyncService critical error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }

        $this->log('Sync completed!');
        return [
            'createdDocuments' => $createdDocuments,
            'updatedDocuments' => $updatedDocuments,
            'createdPositions' => $createdPositions,
            'updatedPositions' => $updatedPositions,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Sync only undelivered shipping documents
     */
    public function syncUndelivered(int $rows = 200, bool $useMock = false, string $mockFile = null): array
    {
        $createdDocuments = 0;
        $updatedDocuments = 0;
        $createdPositions = 0;
        $updatedPositions = 0;
        $skipped = 0;
        $errors = 0;

        if ($useMock) {
            $this->log('Starting undelivered shipping documents sync from MOCK DATA...');
            $mockData = $this->loadMockData($mockFile);
            $mockData = array_filter($mockData, function($row) {
                return ($row['DocEvaso'] ?? true) === false || ($row['RigaEvasa'] ?? true) === false;
            });
            $dataSource = [$mockData];
        } else {
            $this->log('Starting undelivered shipping documents sync (DocEvaso = false OR RigaEvasa = false)...');
            $where = "(DocEvaso = 0 OR RigaEvasa = 0)";
            $dataSource = $this->api->paginate('xbtvw_B2B_DVI', $rows, $where);
        }

        try {
            foreach ($dataSource as $pageData) {
                try {
                    if ($useMock) {
                        $list = $pageData;
                    } else {
                        $list = Arr::get($pageData, 'result', []);
                    }

                    if (empty($list)) {
                        continue;
                    }

                    $this->log('Processing batch of '.count($list).' undelivered items...');

                    foreach ($list as $row) {
                        try {
                            $idDOTes = (int)($row['ID_DOTes'] ?? 0);
                            $idDORig = (int)($row['ID_DORig'] ?? 0);
                            $numeroDoc = $this->safeString($row['NumeroDoc'] ?? '');
                            $cdAR = $this->safeString($row['CD_AR'] ?? '');

                            if (!$idDORig || !$numeroDoc || !$cdAR) {
                                $skipped++;
                                continue;
                            }

                            $documentResult = $this->findOrCreateDocument($row, $idDOTes);

                            if (!$documentResult['document']) {
                                $skipped++;
                                continue;
                            }

                            $document = $documentResult['document'];
                            if ($documentResult['created']) {
                                $createdDocuments++;
                            } elseif ($documentResult['updated']) {
                                $updatedDocuments++;
                            }

                            $positionResult = $this->findOrCreatePosition($document, $row, $idDORig);
                            if ($positionResult['created']) {
                                $createdPositions++;
                            } elseif ($positionResult['updated']) {
                                $updatedPositions++;
                            } else {
                                $skipped++;
                            }
                        } catch (\Throwable $e) {
                            $errors++;
                            $this->log('Error processing row: '.$e->getMessage(), 'error');
                            Log::error('ShippingDocumentsSyncService (undelivered) error: '.$e->getMessage(), [
                                'row' => $row ?? null,
                                'trace' => $e->getTraceAsString(),
                            ]);
                        }
                    }
                } catch (\RuntimeException $e) {
                    if (strpos($e->getMessage(), 'timeout') !== false) {
                        $this->log('Timeout error on page: '.$e->getMessage(), 'warning');
                        break;
                    }
                    throw $e;
                }
            }
        } catch (\Throwable $e) {
            $this->log('Critical error during undelivered sync: '.$e->getMessage(), 'error');
            Log::error('ShippingDocumentsSyncService critical error (undelivered): '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }

        $this->log('Undelivered shipping documents sync completed!');
        return [
            'createdDocuments' => $createdDocuments,
            'updatedDocuments' => $updatedDocuments,
            'createdPositions' => $createdPositions,
            'updatedPositions' => $updatedPositions,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Find or create ShippingDocument
     */
    protected function findOrCreateDocument(array $row, int $idDOTes): array
    {
        $numeroDoc = $this->safeString($row['NumeroDoc'] ?? '');
        $dataDoc = $row['DataDoc'] ?? null;
        $cdCF = $this->safeString($row['CD_CF'] ?? '');
        $cdPG = $this->safeString($row['CD_PG'] ?? '');
        $cdDO = $this->safeString($row['CD_DO'] ?? '');
        $descTipoDoc = $this->safeString($row['DescTipoDoc'] ?? '');
        $docEvaso = (bool)($row['DocEvaso'] ?? false);
        $righe = (int)($row['Righe'] ?? 0);
        $totImponibileE = $this->toFloat($row['TotImponibileE'] ?? 0);
        $totDocumentoE = $this->toFloat($row['TotDocumentoE'] ?? 0);

        $document = ShippingDocument::where('seipee_document_id', (string)$idDOTes)->first();

        $created = false;
        $updated = false;

        if (!$document) {
            $document = new ShippingDocument();
            $document->seipee_document_id = (string)$idDOTes;
            $document->document_number = $numeroDoc;
            $document->document_type_code = $cdDO;
            $document->document_type_description = $descTipoDoc;
            $document->customer_code = $cdCF;
            $document->payment_type = $cdPG;
            $document->rows_count = $righe;
            $document->total_excl_vat = $totImponibileE;
            $document->total_incl_vat = $totDocumentoE;
            $document->is_fully_delivered = $docEvaso;

            if ($dataDoc) {
                try {
                    $document->document_date = \Carbon\Carbon::parse($dataDoc);
                } catch (\Exception $e) {}
            }

            $user = User::where('external_id', $cdCF)->first();
            if ($user) {
                $document->user_id = $user->id;
            }

            $document->save();
            $created = true;

            $this->log('Created shipping document: '.$numeroDoc.' (Seipee ID: '.$idDOTes.')');
        } else {
            if ($document->document_number !== $numeroDoc) {
                $document->document_number = $numeroDoc;
                $updated = true;
            }

            if (!$document->user_id && $cdCF) {
                $user = User::where('external_id', $cdCF)->first();
                if ($user) {
                    $document->user_id = $user->id;
                    $updated = true;
                }
            }

            if ($cdPG && $document->payment_type !== $cdPG) {
                $document->payment_type = $cdPG;
                $updated = true;
            }

            if ($document->is_fully_delivered !== $docEvaso) {
                $document->is_fully_delivered = $docEvaso;
                $updated = true;
            }

            if ($document->rows_count !== $righe) {
                $document->rows_count = $righe;
                $updated = true;
            }

            if ($updated) {
                $document->save();
                $this->log('Updated shipping document: '.$numeroDoc);
            }
        }

        return [
            'document' => $document,
            'created' => $created,
            'updated' => $updated,
        ];
    }

    /**
     * Find or create ShippingDocumentPosition
     */
    protected function findOrCreatePosition(ShippingDocument $document, array $row, int $idDORig): array
    {
        $cdAR = $this->safeString($row['CD_AR'] ?? '');
        $descrizione = $this->safeString($row['Descrizione'] ?? '');
        $variante = $this->extractVariant($row['Variante'] ?? null);
        $cdARMisura = $this->safeString($row['Cd_ARMisura'] ?? 'NR');
        $qta = $this->toFloat($row['Qta'] ?? 0);
        $qtaEvadibile = $this->toFloat($row['QtaEvadibile'] ?? 0);
        $prezzoUnitario = $this->toFloat($row['PrezzoUnitarioV'] ?? 0);
        $prezzoTotale = $this->toFloat($row['PrezzoTotaleV'] ?? 0);
        $scontoRiga = $this->safeString($row['ScontoRiga'] ?? '');
        $dataConsegna = $row['DataConsegna'] ?? null;
        $rigaEvasa = (bool)($row['RigaEvasa'] ?? false);
        $idRigaOrdine = (int)($row['ID_RigaOrdine'] ?? 0);

        $offer = Offer::where('code', $cdAR)->first();

        $position = ShippingDocumentPosition::where('shipping_document_id', $document->id)
            ->where('seipee_position_id', $idDORig)
            ->first();

        $created = false;
        $updated = false;

        if (!$position) {
            $position = new ShippingDocumentPosition();
            $position->shipping_document_id = $document->id;
            $position->seipee_position_id = $idDORig;
            $created = true;
        } else {
            $updated = true;
        }

        $position->product_code = $cdAR;
        $position->description = $descrizione;
        $position->variant = $variante;
        $position->unit_of_measure = $cdARMisura;
        $position->quantity = $qta;
        $position->deliverable_quantity = $qtaEvadibile;
        $position->unit_price = $prezzoUnitario ?? 0;
        $position->total_price = $prezzoTotale;
        $position->discount = $scontoRiga;
        $position->is_fully_delivered = $rigaEvasa;

        if ($offer) {
            $position->offer_id = $offer->id;
        }

        if ($idRigaOrdine) {
            $orderPosition = OrderPosition::whereRaw("JSON_EXTRACT(property, '$.seipee_row_id') = ?", [$idRigaOrdine])->first();
            if ($orderPosition) {
                $position->order_position_id = $orderPosition->id;
            }
        }

        if ($dataConsegna) {
            try {
                $position->delivery_date = \Carbon\Carbon::parse($dataConsegna);
            } catch (\Exception $e) {}
        }

        $position->save();

        if ($created) {
            $this->log('Created position: '.$cdAR.' for document '.$document->document_number);
        } elseif ($updated) {
            $this->log('Updated position: '.$cdAR.' for document '.$document->document_number);
        }

        return [
            'created' => $created,
            'updated' => $updated,
        ];
    }

    protected function loadMockData(string $mockFile = null): array
    {
        if ($mockFile === null) {
            $mockFile = plugins_path('lovata/apisynchronization/mock_shipping_documents.json');
        } elseif (!file_exists($mockFile)) {
            $relativePath = plugins_path('lovata/apisynchronization/' . $mockFile);
            if (file_exists($relativePath)) {
                $mockFile = $relativePath;
            }
        }

        if (!file_exists($mockFile)) {
            throw new \RuntimeException('Mock data file not found: ' . $mockFile);
        }

        $this->log('Loading mock data from: ' . $mockFile);

        $content = file_get_contents($mockFile);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON in mock file: ' . json_last_error_msg());
        }

        if (!is_array($data)) {
            throw new \RuntimeException('Mock data must be an array');
        }

        $this->log('Loaded ' . count($data) . ' mock records');

        return $data;
    }

    protected function extractVariant($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            return trim($value);
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        return (string)$value;
    }

    protected function toFloat($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_array($value) || is_object($value)) {
            return null;
        }
        if (is_numeric($value)) {
            return (float)$value;
        }
        $v = str_replace([' ', ','], ['', '.'], (string)$value);
        return is_numeric($v) ? (float)$v : null;
    }

    protected function safeString($value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_array($value) || is_object($value)) {
            return '';
        }
        return trim((string)$value);
    }

    protected function log(string $message, string $level = 'info'): void
    {
        if ($this->console) {
            if ($level === 'error') {
                $this->console->error($message);
            } elseif ($level === 'warning') {
                $this->console->warn($message);
            } else {
                $this->console->info($message);
            }
        }
    }
}
