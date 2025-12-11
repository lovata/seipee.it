<?php namespace Lovata\Basecode\Classes\Event\User;

use Lovata\Buddies\Models\User;

/**
 * Class UserModelHandler
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
    }
}
