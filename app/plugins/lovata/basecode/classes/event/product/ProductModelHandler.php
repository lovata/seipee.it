<?php namespace Lovata\Basecode\Classes\Event\Product;

use Log;
use Lovata\Shopaholic\Classes\Item\ProductItem;
use Media\Classes\MediaLibrary;
use System\Models\File;

/**
 * Class ProductModelHandler
 * @package Lovata\Basecode\Classes\Event\Product
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
    }
}
