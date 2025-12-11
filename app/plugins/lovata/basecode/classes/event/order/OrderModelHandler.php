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
    }
}
