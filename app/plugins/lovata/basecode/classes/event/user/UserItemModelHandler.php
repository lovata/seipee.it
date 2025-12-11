<?php namespace Lovata\Basecode\Classes\Event\User;

use Lovata\Buddies\Classes\Item\UserItem;

/**
 * Class UserItemModelHandler
 * @package Lovata\Basecode\Classes\Event\User
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class UserItemModelHandler
{
    CONST B2B_PERMISSION_ALLOWED = 1;

    /**
     * Add listeners
     */
    public function subscribe()
    {
        UserItem::extend(function (UserItem $obItem) {
            $obItem->addDynamicMethod('getCanOrderAttribute', function () use ($obItem) {
                $user = $obItem->getObject();
                return empty($user->parent_id) || $user->b2b_permission == self::B2B_PERMISSION_ALLOWED;
            });
            $obItem->addDynamicMethod('getIsCompanyAdminAttribute', function () use ($obItem) {
                $user = $obItem->getObject();
                return empty($user->parent_id);
            });
            $obItem->addDynamicMethod('getParentAttribute', function () use ($obItem) {
                $user = $obItem->getObject();

                if (empty($user->parent)) {
                    return null;
                }

                return UserItem::make($user->parent->id);
            });
        });
    }
}
