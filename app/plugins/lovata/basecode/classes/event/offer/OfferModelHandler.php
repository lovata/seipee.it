<?php namespace Lovata\Basecode\Classes\Event\Offer;

use Lovata\ApiSynchronization\classes\OfferPriceService;
use Lovata\Shopaholic\Classes\Item\OfferItem;

/**
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class OfferModelHandler
{
    /**
     * Add listeners
     */
    public function subscribe()
    {
        OfferItem::extend(function (OfferItem $obOfferItem) {
            $obOfferItem->addDynamicMethod('getPriceValueAttribute', function () use ($obOfferItem) {
                $offerPriceService = new OfferPriceService();
                $obPriceStore = $offerPriceService->loadPrice($obOfferItem);

                return $obPriceStore?->netPrice;
            });
        });
        OfferItem::extend(function (OfferItem $obOfferItem) {
            $obOfferItem->addDynamicMethod('getOldPriceValueAttribute', function () use ($obOfferItem) {
                $offerPriceService = new OfferPriceService();
                $obPriceStore = $offerPriceService->loadPrice($obOfferItem);

                return $obPriceStore?->price;
            });
        });
    }
}
