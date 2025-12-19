<?php namespace Lovata\ApiSynchronization\Classes;

use Lovata\ApiSynchronization\Models\ProductAlias;
use Lovata\Shopaholic\Models\Product;
use Lovata\Buddies\Models\User;
use Illuminate\Console\Command;

/**
 * ProductAliasesSyncService
 * Syncs product aliases (alternative codes) from xbtvw_B2B_CodAlt API endpoint.
 * Links products and users by their respective codes.
 */
class ProductAliasesSyncService
{
    /** @var ApiClientService */
    protected $api;

    /** @var Command|null */
    protected $console;

    public function __construct(ApiClientService $api, Command $console = null)
    {
        $this->api = $api;
        $this->console = $console;
    }

    /**
     * Sync product aliases from xbtvw_B2B_CodAlt.
     * Creates or updates ProductAlias records and links them to products and users.
     *
     * @param int $rows Rows per page
     * @return array Statistics
     */
    public function sync(int $rows = 200): array
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        if ($this->console) {
            $this->console->info('Pre-loading products and users...');
        }

        $productMap = $this->buildProductMap();
        $userMap = $this->buildUserMap();

        if ($this->console) {
            $this->console->info(sprintf('Loaded %d products and %d users', count($productMap), count($userMap)));
        }

        foreach ($this->api->paginate('xbtvw_B2B_CodAlt', $rows) as $page) {
            $items = $page['result'] ?? [];
            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $row) {
                $externalProductId = $this->safeString($row['CodiceArticolo'] ?? null);
                $externalUserId = $this->safeString($row['CodiceCliente'] ?? null);
                $codiceAlternativo = $this->safeString($row['CodiceAlternativo'] ?? null);

                if (!$externalProductId || !$externalUserId || !$codiceAlternativo) {
                    $skipped++;
                    if ($this->console) {
                        $this->console->warn('Skipping alias: missing required fields');
                    }
                    continue;
                }

                try {
                    $productId = $productMap[$externalProductId] ?? null;
                    if (!$productId) {
                        $skipped++;
                        if ($this->console) {
                            $this->console->warn("Product not found for external ID: {$externalProductId}");
                        }
                        continue;
                    }

                    $userId = $userMap[$externalUserId] ?? null;
                    if (!$userId) {
                        $skipped++;
                        if ($this->console) {
                            $this->console->warn("User not found for external ID: {$externalUserId}");
                        }
                        continue;
                    }

                    $productAlias = ProductAlias::findOrCreateByIds($productId, $userId);
                    $isNew = !$productAlias->exists;

                    $changed = false;
                    if ($productAlias->alias !== $codiceAlternativo) {
                        $productAlias->alias = $codiceAlternativo;
                        $changed = true;
                    }

                    if ($isNew || $changed) {
                        $productAlias->save();
                        if ($isNew) {
                            $created++;
                            if ($this->console) {
                                $this->console->line("Created alias: {$codiceAlternativo} for product ID {$productId} and user ID {$userId}");
                            }
                        } else {
                            $updated++;
                        }
                    } else {
                        $skipped++;
                    }

                } catch (\Throwable $e) {
                    $errors++;
                    if ($this->console) {
                        $this->console->error("Failed to sync alias {$externalProductId}/{$externalUserId}: {$e->getMessage()}");
                    }
                }
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Build a map of external product IDs to internal product IDs.
     * @return array [external_id => internal_id]
     */
    protected function buildProductMap(): array
    {
        return Product::whereNotNull('external_id')
            ->pluck('id', 'external_id')
            ->toArray();
    }

    /**
     * Build a map of external user IDs to internal user IDs.
     * @return array [external_id => internal_id]
     */
    protected function buildUserMap(): array
    {
        return User::whereNotNull('external_id')
            ->pluck('id', 'external_id')
            ->toArray();
    }

    /**
     * Safely extract and trim string value.
     */
    protected function safeString($value): ?string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }
        $trimmed = trim((string)$value);
        return $trimmed === '' ? null : $trimmed;
    }
}

