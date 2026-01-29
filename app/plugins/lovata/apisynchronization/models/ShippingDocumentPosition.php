<?php namespace Lovata\ApiSynchronization\Models;

use Model;
use Lovata\OrdersShopaholic\Models\Order;
use Lovata\OrdersShopaholic\Models\OrderPosition;
use Lovata\Shopaholic\Models\Offer;

/**
 * Class ShippingDocumentPosition
 * @package Lovata\ApiSynchronization\Models
 *
 * @property int $id
 * @property int $shipping_document_id
 * @property int $seipee_position_id
 * @property int $order_position_id
 * @property int $offer_id
 * @property string $product_code
 * @property string $description
 * @property string $variant
 * @property string $unit_of_measure
 * @property float $quantity
 * @property float $deliverable_quantity
 * @property float $unit_price
 * @property float $total_price
 * @property string $discount
 * @property \Carbon\Carbon $delivery_date
 * @property bool $is_fully_delivered
 * @property array $property
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property ShippingDocument $shipping_document
 * @property OrderPosition $order_position
 * @property Offer $offer
 * @property Order $order
 */
class ShippingDocumentPosition extends Model
{
    public $table = 'lovata_apisync_shipping_document_positions';

    public $fillable = [
        'shipping_document_id',
        'seipee_position_id',
        'order_position_id',
        'offer_id',
        'product_code',
        'description',
        'variant',
        'unit_of_measure',
        'quantity',
        'deliverable_quantity',
        'unit_price',
        'total_price',
        'discount',
        'delivery_date',
        'is_fully_delivered',
        'property',
    ];

    public $jsonable = ['property'];

    public $dates = ['delivery_date', 'created_at', 'updated_at'];

    public $casts = [
        'is_fully_delivered' => 'boolean',
        'quantity' => 'float',
        'deliverable_quantity' => 'float',
        'unit_price' => 'float',
        'total_price' => 'float',
    ];

    public $belongsTo = [
        'shipping_document' => [ShippingDocument::class, 'key' => 'shipping_document_id'],
        'order_position' => [OrderPosition::class, 'key' => 'order_position_id'],
        'offer' => [Offer::class, 'key' => 'offer_id'],
    ];

    /**
     * Get related order
     *
     * @return Order|null
     */
    public function getOrderAttribute()
    {
        return $this->order_position ? $this->order_position->order : null;
    }
}
