<?php namespace Lovata\BaseCode\Components;

use Lovata\OrdersShopaholic\Components\OrderPage;
use Lovata\Toolbox\Classes\Helper\UserHelper;

use Lovata\OrdersShopaholic\Models\Order;

/**
 * Class OrderPageCustom
 * @package Lovata\BaseCode\Components
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class OrderPageCustom extends OrderPage
{
    /** @var \Lovata\Buddies\Models\User */
    public $obUser;

    protected function getElementObject($sElementSlug)
    {
        if (empty($sElementSlug)) {
            return null;
        }

        $this->obUser = UserHelper::instance()->getUser();

        if (!empty($this->obUser) && !empty($this->obUser->parent)) {
            $this->obUser = $this->obUser->parent;
        }

        $obElement = Order::getBySecretKey($sElementSlug)->first();

        if (!empty($obElement) && !empty($this->obUser) && $obElement->user_id != $this->obUser->id) {
            $obElement = null;
        }

        return $obElement;
    }
}
