<?php namespace Lovata\PopularityShopaholic\Classes\Event;

use Lovata\Toolbox\Classes\Event\ModelHandler;

use Lovata\Shopaholic\Models\Product;
use Lovata\Shopaholic\Classes\Item\ProductItem;
use Lovata\Shopaholic\Classes\Store\ProductListStore;

/**
 * Class ProductModelHandler
 * @package Lovata\PopularityShopaholic\Classes\Event
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ProductModelHandler extends ModelHandler
{
    /** @var  Product */
    protected $obElement;

    /**
     * Add listeners
     * @param \Illuminate\Events\Dispatcher $obEvent
     */
    public function subscribe($obEvent)
    {
        parent::subscribe($obEvent);

        Product::extend(function ($obElement) {
            /** @var Product $obElement */
            $obElement->fillable[] = 'popularity';
        });

        $obEvent->listen('shopaholic.sorting.get.list', function ($sSorting) {
            return $this->getSortingList($sSorting);
        });
    }

    /**
     * Get model class name
     * @return string
     */
    protected function getModelClass()
    {
        return Product::class;
    }

    /**
     * Get item class name
     * @return string
     */
    protected function getItemClass()
    {
        return ProductItem::class;
    }

    /**
     * After create event handler
     */
    protected function afterCreate()
    {
        ProductListStore::instance()->sorting->clear(ProductListStore::SORT_POPULARITY_DESC);
    }

    /**
     * After save event handler
     */
    protected function afterSave()
    {
        //Check "popularity" field
        if ($this->obElement->getOriginal('popularity') == $this->obElement->popularity) {
            return;
        }

        //Update product list with popularity
        ProductListStore::instance()->sorting->clear(ProductListStore::SORT_POPULARITY_DESC);
    }

    /**
     * After delete event handler
     */
    protected function afterDelete()
    {
        ProductListStore::instance()->sorting->clear(ProductListStore::SORT_POPULARITY_DESC);
    }

    /**
     * Get sorting by popularity
     * @param string $sSorting
     * @return array|null
     */
    protected function getSortingList($sSorting)
    {
        if (empty($sSorting) || $sSorting != ProductListStore::SORT_POPULARITY_DESC) {
            return null;
        }

        /** @var array $arProductIDList */
        $arProductIDList = Product::orderBy('popularity', 'desc')->toBase()->pluck('id')->all();

        return $arProductIDList;
    }
}
