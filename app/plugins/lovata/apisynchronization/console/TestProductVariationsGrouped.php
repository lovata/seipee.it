<?php namespace Lovata\ApiSynchronization\console;

use Illuminate\Console\Command;
use Lovata\Shopaholic\Models\Product;
use Symfony\Component\Console\Input\InputArgument;

class TestProductVariationsGrouped extends Command
{
    protected $name = 'seipee:test-variations-grouped';

    protected $description = 'Test getGroupedVariations() method on a product';

    public function handle()
    {
        $identifier = $this->argument('product');

        // Find product
        $product = null;
        if (is_numeric($identifier)) {
            $product = Product::find((int)$identifier);
        }

        if (!$product) {
            $product = Product::where('name', 'LIKE', '%' . $identifier . '%')
                ->orWhere('code', 'like', '%' . $identifier . '%')
                ->first();
        }

        if (!$product) {
            $this->error('Product not found: ' . $identifier);
            return 1;
        }

        $this->info('Product: ' . $product->name . ' (ID: ' . $product->id . ')');
        $this->info('External ID: ' . ($product->external_id ?? 'N/A'));
        $this->line('');

        // Get variation options (unique values by property)
        $options = $product->getVariationOptions();

        if (empty($options)) {
            $this->warn('No variations found for this product.');
            return 0;
        }

        $this->line('┌─────────────────────────────────────────────────┐');
        $this->line('│ VARIATION OPTIONS (for frontend selectors)     │');
        $this->line('└─────────────────────────────────────────────────┘');
        $this->line('');

        foreach ($options as $propertyData) {
            $this->info('  ' . $propertyData['property_name'] . ' (' . $propertyData['property_code'] . ')');
            foreach ($propertyData['values'] as $valueData) {
                $this->line('    • ' . $valueData['value']);
            }
            $this->line('');
        }

        // Get grouped variations
        $variations = $product->getGroupedVariations();

        $this->line('Grouped variations (getGroupedVariations):');
        $this->line(json_encode($variations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return 0;
    }

    public function getArguments()
    {
        return [
            ['product', InputArgument::REQUIRED, 'Product ID or name (partial match)'],
        ];
    }
}
