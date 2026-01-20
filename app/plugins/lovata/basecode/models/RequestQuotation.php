<?php namespace Lovata\BaseCode\Models;

use Model;

/**
 * Model
 */
class RequestQuotation extends Model
{
    use \October\Rain\Database\Traits\Validation;


    public $table = 'lovata_basecode_request_quotations';

    public $rules = [
        'title'                 => 'required|max:255',
        'notes'                 => 'required|max:255',
        'variants'              => 'array',
        'product_id'            => 'required|exists:lovata_shopaholic_products,id',
        'user_id'               => 'required|exists:lovata_buddies_users,id'
    ];

    public $fillable = [
        'title',
        'notes',
        'variants',
        'product_id',
        'user_id',
    ];

    public $jsonable = ['variants'];

    public $belongsTo = [
        'product' => ['Lovata\Shopaholic\Models\Product'],
        'user' => ['Lovata\Buddies\Models\User'],
    ];

}
