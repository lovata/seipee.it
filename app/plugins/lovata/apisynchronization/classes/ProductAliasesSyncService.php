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

        foreach ($this->api->paginate('xbtvw_B2B_CodAlt', $rows) as $page) {
            $items = $page['result'] ?? [];
            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $row) {
                $codiceArticolo = $this->safeString($row['CodiceArticolo'] ?? null);
                $codiceCliente = $this->safeString($row['CodiceCliente'] ?? null);
                $codiceAlternativo = $this->safeString($row['CodiceAlternativo'] ?? null);

                if (!$codiceArticolo || !$codiceCliente || !$codiceAlternativo) {
                    $skipped++;
                    if ($this->console) {
                        $this->console->warn('Skipping alias: missing required fields');
                    }
                    continue;
                }

                try {
                    // Find product by external_id
                    $product = Product::where('external_id', $codiceArticolo)->first();
                    if (!$product) {
                        $skipped++;
                        if ($this->console) {
                            $this->console->warn("Product not found for CodiceArticolo: {$codiceArticolo}");
                        }
                        continue;
                    }

                    // Find user by external_id (ARCA Customer Code)
                    $user = User::where('external_id', $codiceCliente)->first();
                    if (!$user) {
                        $skipped++;
                        if ($this->console) {
                            $this->console->warn("User not found for CodiceCliente: {$codiceCliente}");
                        }
                        continue;
                    }

                    // Find or create alias by product_id and user_id
                    $productAlias = ProductAlias::findOrCreateByIds($product->id, $user->id);
                    $isNew = !$productAlias->exists;

                    // Update alias if different
                    $changed = false;
                    if ($productAlias->alias !== $codiceAlternativo) {
                        $productAlias->alias = $codiceAlternativo;
                        $changed = true;
                    }

                    // Save if new or changed
                    if ($isNew || $changed) {
                        $productAlias->save();
                        if ($isNew) {
                            $created++;
                            if ($this->console) {
                                $this->console->line("Created alias: {$codiceAlternativo} for product {$codiceArticolo} and customer {$codiceCliente}");
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
                        $this->console->error("Failed to sync alias {$codiceArticolo}/{$codiceCliente}: {$e->getMessage()}");
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

