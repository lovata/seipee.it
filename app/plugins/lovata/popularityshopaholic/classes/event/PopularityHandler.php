<?php namespace Lovata\PopularityShopaholic\Classes\Event;

use System\Classes\PluginManager;

use Lovata\Shopaholic\Models\Offer;
use Lovata\Shopaholic\Models\Product;
use Lovata\Shopaholic\Models\Settings;
use Lovata\Shopaholic\Classes\Store\ProductListStore;

/**
 * Class PopularityHandler
 * @package Lovata\PopularityShopaholic\Classes\Event
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class PopularityHandler
{
    /**
     * Add listeners
     * @param \Illuminate\Events\Dispatcher $obEvent
     */
    public function subscribe($obEvent)
    {
        $obEvent->listen('shopaholic.product.open', function ($obProduct) {
            $this->eventProductOpen($obProduct);
        });

        if (PluginManager::instance()->hasPlugin('Lovata.OrdersShopaholic')) {
            $obEvent->listen('shopaholic.cart.add', function ($iOfferID) {
                $this->eventCartAdd($iOfferID);
            });

            $obEvent->listen('shopaholic.order.created', function ($obOrder) {
                $this->eventOrderCreated($obOrder);
            });
        }
    }

    /**
     * Add point, where product page opened
     * @param Product $obProduct
     */
    protected function eventProductOpen($obProduct)
    {
        /** @var Product $obProduct */
        if (empty($obProduct) || !$obProduct instanceof Product) {
            return;
        }

        //Get point
        $iPoint = (int) Settings::getValue('popularity_open_product');
        if ($iPoint < 0) {
            $iPoint = 0;
        }

        Product::where('id', $obProduct->id)->increment('popularity', $iPoint);
        ProductListStore::instance()->sorting->clear(ProductListStore::SORT_POPULARITY_DESC);
    }

    /**
     * Add point, where product add to cart
     * @param integer $iOfferID
     */
    protected function eventCartAdd($iOfferID)
    {
        if (empty($iOfferID)) {
            return;
        }

        //Get point
        $iPoint = (int) Settings::getValue('popularity_cart_add');
        if ($iPoint < 0) {
            $iPoint = 0;
        }

        //Get offer object
        /** @var Offer $obOffer */
        $obOffer = Offer::find($iOfferID);
        if (empty($obOffer)) {
            return;
        }

        Product::where('id', $obOffer->product_id)->increment('popularity', $iPoint);
        ProductListStore::instance()->sorting->clear(ProductListStore::SORT_POPULARITY_DESC);
    }

    /**
     * Add point, where order created
     * @param \Lovata\OrdersShopaholic\Models\Order $obOrder
     */
    protected function eventOrderCreated($obOrder)
    {
        /** @var \Lovata\OrdersShopaholic\Models\Order $obOrder */
        if (empty($obOrder) || !$obOrder instanceof \Lovata\OrdersShopaholic\Models\Order) {
            return;
        }

        //Get point
        $iPoint = (int) Settings::getValue('popularity_order_create');
        if ($iPoint < 0) {
            $iPoint = 0;
        }

        $obPositionList = $obOrder->order_position;
        if ($obPositionList->isEmpty()) {
            return;
        }

        foreach ($obPositionList as $obPosition) {
            if ($obPosition->item_type != Offer::class) {
                continue;
            }

            $obOffer = $obPosition->item;
            if (empty($obOffer)) {
                continue;
            }

            Product::where('id', $obOffer->product_id)->increment('popularity', $iPoint);
        }

        ProductListStore::instance()->sorting->clear(ProductListStore::SORT_POPULARITY_DESC);
    }
}
