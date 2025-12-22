<?php namespace Lovata\Basecode\Classes\Event\order;

use Lang;
use Kharanenka\Helper\Result;
use Lovata\ApiSynchronization\classes\OfferPriceService;
use Lovata\OrdersShopaholic\Classes\Processor\OrderProcessor;
use Lovata\OrdersShopaholic\Models\Order;

/**
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class OrderModelHandler
{
    /**
     * Add listeners
     * @param \Illuminate\Events\Dispatcher $obEvent
     */
    public function subscribe($obEvent)
    {
        $obEvent->listen(OrderProcessor::EVENT_UPDATE_ORDER_AFTER_CREATE, function(Order $obOrder) {
            foreach ($obOrder->order_position as $obOrderPosition) {
                $obOffer = $obOrderPosition->offer;
                $offerPriceService = new OfferPriceService();
                $obPriceStore = $offerPriceService->loadPrice($obOffer);
                if (empty($obPriceStore)) {
                    Result::setFalse()->setMessage(Lang::get('lovata.apisynchronization::lang.error.load_price_error'));

                    return;
                }

                $obOrderPosition->price = $obPriceStore->netPrice;
                $obOrderPosition->old_price = $obPriceStore->price;
                $obOrderPosition->save();
            }
        });

        $obEvent->listen(OrderProcessor::EVENT_UPDATE_ORDER_AFTER_CREATE, function(Order $obOrder) {
            $obUser = $obOrder->user;

            if (!$obUser->can_order) {
                Result::setFalse()->setMessage(Lang::get('lovata.basecode::lang.error.no_order_access'));

                return;
            }

            if (!empty($obUser->parent)) {

                $arOrderDataProperty['parent_name'] = $obUser->name ?? '';
                $arOrderDataProperty['parent_last_name'] = $obUser->last_name ?? '';

                $obUser = $obUser->parent;

                $obOrder->user_id = $obUser->id;

                $arOrderDataProperty['email'] = $obUser->email ?? '';
                $arOrderDataProperty['name'] = $obUser->name ?? '';
                $arOrderDataProperty['last_name'] = $obUser->last_name ?? '';
                $arOrderDataProperty['middle_name'] = $obUser->middle_name ?? '';
                $arOrderDataProperty['phone'] = $obUser->phone ?? '';


                $obOrder->property = $arOrderDataProperty;
            }
        });
    }
}
