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
        User::extend(function (User $obUser) {
            $obUser->addFillable(['parent_id', 'b2b_permission']);

            $obUser->belongsTo['parent'] = [
                User::class,
                'key'   => 'parent_id',
                'otherKey' => 'id'
            ];

            $obUser->hasMany['children'] = [
                User::class,
                'key' => 'parent_id'
            ];
        });
    }
}
