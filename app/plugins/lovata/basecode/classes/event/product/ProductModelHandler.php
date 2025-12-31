<?php namespace Lovata\Basecode\Classes\Event\Product;

use App;
use Lovata\ApiSynchronization\Models\ProductAlias;
use Lovata\Buddies\Facades\AuthHelper;
use Lovata\Shopaholic\Classes\Collection\ProductCollection;
use Lovata\Shopaholic\Classes\Item\ProductItem;
use Lovata\Shopaholic\Models\Offer;
use Lovata\Shopaholic\Models\Product;
use Media\Classes\MediaLibrary;
use Lovata\Shopaholic\Models\Settings;
use Lovata\SearchShopaholic\Classes\Helper\SearchHelper;

/**
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ProductModelHandler
{
    CONST SERIES_PROPERTY_ID = 16;
    CONST PATH_IMAGE_SERIES = 'images/series/';

    /**
     * Add listeners
     */
    public function subscribe()
    {
        ProductItem::extend(function (ProductItem $product) {
            $product->addDynamicMethod('getImageAttribute', function () use ($product) {
                $model = $product->getObject();

                $value = mb_substr($model->property[self::SERIES_PROPERTY_ID], 1) ?? '';
                $filePath = self::PATH_IMAGE_SERIES . $value . '.webp';

                return MediaLibrary::url($filePath);
            });
        });

        ProductItem::extend(function (ProductItem $obItem) {
            $obItem->addDynamicMethod('getAliasesAttribute', function () use ($obItem) {
                $product = $obItem->getObject();

                if (!$product) {
                    return collect();
                }

                $user = AuthHelper::getUser();

                $query = $product->product_aliases();

                if ($user) {
                    $query->where('user_id', $user->id);
                }

                return $query->pluck('alias');
            });
        });

        ProductCollection::extend(function (ProductCollection $obCollection) {

            if (empty($obCollection) || !$obCollection instanceof ProductCollection) {
                return;
            }

            /** @var ProductCollection $obCollection */
            $obCollection->addDynamicMethod('customSearch', function ($sSearch) use ($obCollection) {

                $user = AuthHelper::getUser();

                $priorityIDs = ProductAlias::where('user_id', $user->id)
                    ->where('alias', 'LIKE', '%'.$sSearch.'%')
                    ->pluck('product_id')
                    ->toArray();

                /** @var array $arSettings */
                $arSettings = Settings::getValue('product_search_by');

                /** @var SearchHelper $obSearchHelper */
                $obSearchHelper = App::make(SearchHelper::class, [
                    'sModel' => Product::class
                ]);
                $searchIDs = $obSearchHelper->result($sSearch, $arSettings) ?? [];

                $resultIDs = array_values(array_unique(array_merge(
                    $priorityIDs,
                    $searchIDs
                )));

                return $obCollection->applySorting($resultIDs);
            });
        });
    }
}
