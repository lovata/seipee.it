<?php namespace Lovata\Basecode\Classes\Event\User;

use Lovata\Buddies\Classes\Item\UserItem;
use Lovata\Buddies\Models\User;
use Lovata\OrdersShopaholic\Classes\Collection\OrderCollection;

/**
 * Class TaxModelHandler
 * @package Lovata\Basecode\Classes\Event\User
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class UserModelHandler
{
    CONST B2B_PERMISSION_ALLOWED = 1;

    /**
     * Add listeners
     */
    public function subscribe()
    {
        User::extend(function (User $obUser) {
            $obUser->addFillable(['parent_id', 'b2b_permission']);
        });

        User::extend(function (User $obUser) {
            $obUser->belongsTo['parent'] = [
                User::class,
                'key'   => 'parent_id',
                'otherKey' => 'id'
            ];
        });

        User::extend(function (User $obUser) {
            $obUser->hasMany['children'] = [
                User::class,
                'key' => 'parent_id'
            ];
        });

        User::extend(function (User $obItem) {
            $obItem->addDynamicMethod('getCanOrderAttribute', function () use ($obItem) {
                return empty($obItem->parent_id) || $obItem->b2b_permission == self::B2B_PERMISSION_ALLOWED;
            });
        });

        UserItem::extend(function (UserItem $obItem) {
            $obItem->addDynamicMethod('getCanOrderAttribute', function () use ($obItem) {
                $user = $obItem->getObject();
                return empty($user->parent_id) || $user->b2b_permission == self::B2B_PERMISSION_ALLOWED;
            });
        });

        UserItem::extend(function (UserItem $obItem) {
            $obItem->addDynamicMethod('getIsCompanyAdminAttribute', function () use ($obItem) {
                $user = $obItem->getObject();
                return empty($user->parent_id);
            });
        });

        UserItem::extend(function (UserItem $obItem) {
            $obItem->addDynamicMethod('getParentAttribute', function () use ($obItem) {
                $user = $obItem->getObject();

                if (empty($user->parent)) {
                    return null;
                }

                return UserItem::make($user->parent->id);
            });
        });

        UserItem::extend(function($obUserItem) {
            /** @var \Lovata\Buddies\Classes\Item\UserItem $obUserItem */
            $obUserItem->addDynamicMethod('getOrderAttribute', function ($obUserItem) {
                /** @var \Lovata\Buddies\Classes\Item\UserItem $obUserItem */
                $userId = $obUserItem->parent ? $obUserItem->parent->id : $obUserItem->id;

                $obOrderList = OrderCollection::make()->user($userId);

                return $obOrderList;
            });
        });
    }
}
