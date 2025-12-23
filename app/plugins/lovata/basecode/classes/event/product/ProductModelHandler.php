<?php namespace Lovata\Basecode\Classes\Event\Product;

use Lovata\Buddies\Facades\AuthHelper;
use Lovata\Shopaholic\Classes\Item\ProductItem;
use Media\Classes\MediaLibrary;

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
    }
}
