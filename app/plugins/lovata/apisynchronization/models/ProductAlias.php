<?php namespace Lovata\ApiSynchronization\Models;

use Model;
use Lovata\Shopaholic\Models\Product;
use Lovata\Buddies\Models\User;

/**
 * ProductAlias Model
 * Represents product aliases (alternative codes) for specific customers.
 * Synced from xbtvw_B2B_CodAlt API endpoint.
 */
class ProductAlias extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'lovata_user_product_aliases';

    public $timestamps = true;

    public $fillable = [
        'product_id',
        'user_id',
        'alias',
    ];

    public $rules = [
        'product_id' => 'required|exists:lovata_shopaholic_products,id',
        'user_id' => 'required|exists:lovata_buddies_users,id',
        'alias' => 'required|string|max:255',
    ];

    public $belongsTo = [
        'product' => [
            Product::class,
            'key' => 'product_id',
        ],
        'user' => [
            User::class,
            'key' => 'user_id',
        ],
    ];

    /**
     * Find or create product alias by unique combination of product_id and user_id.
     */
    public static function findOrCreateByIds(int $productId, int $userId): self
    {
        $alias = self::where('product_id', $productId)
            ->where('user_id', $userId)
            ->first();

        if (!$alias) {
            $alias = new self();
            $alias->product_id = $productId;
            $alias->user_id = $userId;
        }

        return $alias;
    }
}
