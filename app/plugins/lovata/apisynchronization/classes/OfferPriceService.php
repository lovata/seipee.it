<?php namespace Lovata\ApiSynchronization\classes;

use Carbon\Carbon;
use Lovata\ApiSynchronization\Dto\PriceResponseDto;
use Lovata\Buddies\Facades\AuthHelper;
use Arr;

class OfferPriceService
{
    /** @var ApiClientService */
    protected $apiClient;

    static array $arPriceStoreList = [];

    public function __construct()
    {
        $this->apiClient = new ApiClientService();
    }

    /**
     * @param \Lovata\Shopaholic\Classes\Item\OfferItem|\Lovata\Shopaholic\Models\Offer $offer
     * @return bool
     */
    public function loadPrice($offer): ?PriceResponseDto
    {
        $obPriceStore = Arr::get(self::$arPriceStoreList, $offer->id);
        if (!empty($obPriceStore)) {
            return $obPriceStore;
        }

        /** @var \Lovata\Buddies\Models\User $obUser */
        $obUser = AuthHelper::getUser();
        if (empty($obUser)) {
            return null;
        }

        if (!empty($obUser->parent)) {
            $obUser = $obUser->parent;
        }

        $sClientCode = $obUser->external_id;
        $sProductCode = $offer->product?->code;
        if (empty($sClientCode) || empty($sProductCode)) {
            return null;
        }

        $sDate = Carbon::now()->format('d/m/Y');

        $sParams = "0,0,'{$sDate}','{$sClientCode}','{$sProductCode}',null,0";

        $obApiClient = new ApiClientService();
        $arPriceData = $obApiClient->getStored('xbtsp_Seipee_CalcoloPrzVend', $sParams);
        $obPriceStore = PriceResponseDto::fromApiResponse($arPriceData);
        self::$arPriceStoreList[$offer->id] = $obPriceStore;

        return $obPriceStore;
    }
}
