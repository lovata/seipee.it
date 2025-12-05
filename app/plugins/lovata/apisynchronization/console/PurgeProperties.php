<?php namespace Lovata\ApiSynchronization\console;

use Illuminate\Console\Command;
use Lovata\PropertiesShopaholic\Models\Property;
use Lovata\PropertiesShopaholic\Models\PropertyValue;
use Lovata\PropertiesShopaholic\Models\PropertyValueLink;
use Lovata\Shopaholic\Models\Product;

class PurgeProperties extends Command
{
    /**
     * php artisan seipee:properties.purge [--all] [--with-products] [--dry-run]
     */
    protected $name = 'seipee:properties.purge';

    protected $description = 'Delete Properties and all related values and product links. By default, only Properties with external_id NOT NULL are targeted. Use --all to wipe ALL properties. Use --with-products to also delete linked products.';

    public function handle()
    {
        $all = (bool) $this->option('all');
        $withProducts = (bool) $this->option('with-products');
        $dryRun = (bool) $this->option('dry-run');

        if ($all) {
            $this->warn('You are about to purge ALL properties'.($withProducts ? ' and related PRODUCTS' : '').'.');
        } else {
            $this->info('Purging ONLY properties with external_id NOT NULL'.($withProducts ? ' and related PRODUCTS' : '').'.');
        }

        if ($dryRun) {
            $this->info('DRY RUN: no data will be deleted.');
        }

        $query = Property::query();
        if (!$all) {
            $query->whereNotNull('external_id')->where('external_id', '!=', '');
        }

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->info('No properties to purge.');
            return 0;
        }

        $this->info('Found '.$total.' properties to purge.');

        $deletedProps = 0;
        $deletedLinks = 0;
        $deletedVariantLinks = 0;
        $deletedValues = 0;
        $deletedProducts = 0;

        $query->chunkById(200, function ($props) use ($dryRun, $withProducts, &$deletedProps, &$deletedLinks, &$deletedVariantLinks, &$deletedValues, &$deletedProducts) {
            foreach ($props as $prop) {
                // Collect product IDs linked via PropertyValueLink
                $links = PropertyValueLink::getByProperty($prop->id)->get();
                $productIds = $links->pluck('product_id')->filter()->unique()->values()->all();

                // Delete property->value variant links
                $valueIds = $prop->property_value()->pluck('lovata_properties_shopaholic_values.id')->all();

                if ($dryRun) {
                    $this->line("Would detach variant links for property #{$prop->id} [values: ".count($valueIds).']');
                    $this->line("Would delete value links for property #{$prop->id} [links: ".$links->count().']');
                } else {
                    if (!empty($valueIds)) {
                        $prop->property_value()->detach($valueIds);
                        $deletedVariantLinks += count($valueIds);
                    }
                    foreach ($links as $link) {
                        $link->delete();
                        $deletedLinks++;
                    }
                }

                // Optionally delete products
                if ($withProducts && !empty($productIds)) {
                    foreach ($productIds as $pid) {
                        /** @var Product|null $product */
                        $product = Product::find($pid);
                        if ($product) {
                            if ($dryRun) {
                                $this->line("Would delete product #{$product->id} [external_id={$product->external_id}]");
                            } else {
                                $product->delete();
                                $deletedProducts++;
                            }
                        }
                    }
                }

                // Finally delete the property itself
                if ($dryRun) {
                    $this->line("Would delete property #{$prop->id} [external_id={$prop->external_id}, name={$prop->name}]");
                } else {
                    $prop->delete();
                    $deletedProps++;
                }

                // Attempt to remove orphan values that have external_id and no more relations
                if (!empty($valueIds)) {
                    $orphans = PropertyValue::whereIn('id', $valueIds)
                        ->whereNotNull('external_id')
                        ->where('external_id', '!=', '')
                        ->whereDoesntHave('property')
                        ->get();
                    foreach ($orphans as $val) {
                        if ($dryRun) {
                            $this->line("Would delete orphan value #{$val->id} [external_id={$val->external_id}, value={$val->value}]");
                        } else {
                            $val->delete();
                            $deletedValues++;
                        }
                    }
                }
            }
        });

        $this->info('Purge finished'.($dryRun ? ' (dry-run)' : '').'.');
        $this->info("Deleted: properties={$deletedProps}, value_links={$deletedLinks}, variant_links_detached={$deletedVariantLinks}, orphan_values_deleted={$deletedValues}, products_deleted={$deletedProducts}");

        return 0;
    }

    public function getOptions()
    {
        return [
            [
                'all', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
                'Purge ALL properties (not only those with external_id).',
            ],
            [
                'with-products', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
                'Also delete linked products.',
            ],
            [
                'dry-run', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
                'Do not delete anything, just show what would happen.',
            ],
        ];
    }
}
