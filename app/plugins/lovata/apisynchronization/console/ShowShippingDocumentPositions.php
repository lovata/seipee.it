<?php namespace Lovata\ApiSynchronization\console;

use Illuminate\Console\Command;
use Lovata\ApiSynchronization\Models\ShippingDocument;
use Symfony\Component\Console\Input\InputArgument;

/**
 * ShowShippingDocumentPositions Command
 */
class ShowShippingDocumentPositions extends Command
{
    protected $name = 'seipee:show-shipping-document-positions';

    protected $description = 'Show all positions for a shipping document by document number';

    public function handle()
    {
        $documentNumber = trim($this->argument('document_number'));

        if (empty($documentNumber)) {
            $this->error('Document number is required');
            return 1;
        }

        $document = ShippingDocument::where('document_number', 'LIKE', '%'.$documentNumber.'%')->first();

        if (!$document) {
            $this->error('Shipping document not found: ' . $documentNumber);
            return 1;
        }

        $this->info('=======================================================================');
        $this->info('Shipping Document: ' . $document->document_number);
        $this->info('=======================================================================');
        $this->line('Seipee ID: ' . $document->seipee_document_id);
        $this->line('Date: ' . ($document->document_date ? $document->document_date->format('Y-m-d') : 'N/A'));
        $this->line('Type: ' . $document->document_type_code . ' - ' . $document->document_type_description);
        $this->line('Customer: ' . $document->customer_code);
        $this->line('Fully Delivered: ' . ($document->is_fully_delivered ? 'Yes' : 'No'));
        $this->line('Total (excl VAT): ' . $document->total_excl_vat);
        $this->line('Total (incl VAT): ' . $document->total_incl_vat);
        $this->line('Positions count: ' . $document->positions->count());

        $relatedOrders = $document->getRelatedOrderNumbersAttribute();
        if (!empty($relatedOrders)) {
            $this->line('Related Orders: ' . implode(', ', $relatedOrders));
        }

        $this->info('');
        $this->info('Positions:');
        $this->info('=======================================================================');

        $positions = $document->positions()->with(['offer', 'order_position.order'])->get();

        if ($positions->isEmpty()) {
            $this->warn('No positions found for this document');
            return 0;
        }

        $headers = [
            'ID',
            'Product Code',
            'Description',
            'Variant',
            'UM',
            'Qty',
            'Deliverable Qty',
            'Unit Price',
            'Total Price',
            'Delivery Date',
            'Fully Delivered',
            'Order #',
        ];

        $rows = [];
        foreach ($positions as $position) {
            $orderNumber = $position->order_position && $position->order_position->order
                ? $position->order_position->order->order_number
                : 'N/A';

            $rows[] = [
                $position->id,
                $position->product_code,
                \Illuminate\Support\Str::limit($position->description, 30),
                $position->variant ?: '-',
                $position->unit_of_measure,
                $position->quantity,
                $position->deliverable_quantity,
                number_format($position->unit_price, 2),
                number_format($position->total_price, 2),
                $position->delivery_date ? $position->delivery_date->format('Y-m-d') : 'N/A',
                $position->is_fully_delivered ? 'Yes' : 'No',
                $orderNumber,
            ];
        }

        $this->table($headers, $rows);

        $this->info('');
        $this->info('Total positions: ' . $positions->count());

        return 0;
    }

    protected function getArguments()
    {
        return [
            ['document_number', InputArgument::REQUIRED, 'The document number to show positions for'],
        ];
    }
}
