<?php namespace Lovata\ApiSynchronization\Models;

use Model;
use Lovata\Buddies\Models\User;
use Lovata\OrdersShopaholic\Models\Order;

/**
 * Class ShippingDocument
 * @package Lovata\ApiSynchronization\Models
 *
 * @property int $id
 * @property string $seipee_document_id
 * @property string $document_number
 * @property \Carbon\Carbon $document_date
 * @property string $document_type_code
 * @property string $document_type_description
 * @property int $user_id
 * @property string $customer_code
 * @property string $payment_type
 * @property int $rows_count
 * @property float $total_excl_vat
 * @property float $total_incl_vat
 * @property bool $is_fully_delivered
 * @property string $pdf_url
 * @property array $property
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property User $user
 * @property \October\Rain\Database\Collection|ShippingDocumentPosition[] $positions
 */
class ShippingDocument extends Model
{
    public $table = 'lovata_apisync_shipping_documents';

    public $fillable = [
        'seipee_document_id',
        'document_number',
        'document_date',
        'document_type_code',
        'document_type_description',
        'user_id',
        'customer_code',
        'payment_type',
        'rows_count',
        'total_excl_vat',
        'total_incl_vat',
        'is_fully_delivered',
        'pdf_url',
        'property',
    ];

    public $jsonable = ['property'];

    public $dates = ['document_date', 'created_at', 'updated_at'];

    public $casts = [
        'is_fully_delivered' => 'boolean',
        'total_excl_vat' => 'float',
        'total_incl_vat' => 'float',
        'rows_count' => 'integer',
    ];

    public $belongsTo = [
        'user' => [User::class, 'foreignKey' => 'user_id'],
    ];

    public $hasMany = [
        'positions' => [ShippingDocumentPosition::class, 'key' => 'shipping_document_id'],
    ];

    /**
     * Get related order numbers
     *
     * @return array
     */
    public function getRelatedOrderNumbersAttribute()
    {
        return $this->positions()
            ->with('order_position.order')
            ->get()
            ->pluck('order_position.order.order_number')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get related orders
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRelatedOrders()
    {
        $orderIds = $this->positions()
            ->with('order_position')
            ->get()
            ->pluck('order_position.order_id')
            ->filter()
            ->unique()
            ->values();

        return Order::whereIn('id', $orderIds)->get();
    }
}
