<?php namespace Lovata\Basecode\Classes\Event\User;

use Lovata\Buddies\Models\User;

/**
 * Class TaxModelHandler
 * @package Lovata\Basecode\Classes\Event\User
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class UserModelHandler
{
    /**
     * Add listeners
     */
    public function subscribe()
    {
        User::extend(function (User $obTax) {
            $obTax->addFillable(['parent_id', 'b2b_permission']);
        });
    }
}
