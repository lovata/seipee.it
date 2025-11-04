<?php namespace Lovata\PopularityShopaholic\Tests\Unit\Collection;

use Lovata\Shopaholic\Classes\Collection\ProductCollection;
use Lovata\Shopaholic\Classes\Store\ProductListStore;
use Lovata\Shopaholic\Models\Product;
use Lovata\Toolbox\Tests\CommonTest;

use Lovata\ReviewsShopaholic\Classes\Collection\ReviewCollection;

/**
 * Class ProductCollectionTest
 * @package Lovata\PopularityShopaholic\Tests\Unit\Collection
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 *
 * @mixin \PHPUnit\Framework\Assert
 */
class ProductCollectionTest extends CommonTest
{
    /** @var  Product */
    protected $obElement;

    protected $arCreateData = [
        'name'         => 'name',
        'slug'         => 'slug',
        'code'         => 'code',
        'preview_text' => 'preview_text',
        'description'  => 'description',
    ];

    /**
     * Check item collection "sort" method by popularity
     */
    public function testSortingByPopularity()
    {
        $this->createTestData(1);
        if (empty($this->obElement)) {
            return;
        }

        $sErrorMessage = 'Product collection "sort" method is not correct';

        //Check item collection after create
        $obCollection = ProductCollection::make()->sort(ProductListStore::SORT_POPULARITY_DESC);
        self::assertEquals([$this->obElement->id], array_values($obCollection->getIDList()), $sErrorMessage);

        $this->createTestData(2);
        $obCollection = ProductCollection::make()->sort(ProductListStore::SORT_POPULARITY_DESC);

        self::assertEquals([$this->obElement->id, $this->obElement->id - 1], array_values($obCollection->getIDList()), $sErrorMessage);

        $obProduct = Product::find($this->obElement->id - 1);
        $obProduct->popularity = 3;
        $obProduct->save();

        $obCollection = ProductCollection::make()->sort(ProductListStore::SORT_POPULARITY_DESC);

        self::assertEquals([$this->obElement->id - 1, $this->obElement->id], array_values($obCollection->getIDList()), $sErrorMessage);

        $this->obElement->delete();

        //Check item collection, after element remove
        $obCollection = ProductCollection::make()->sort(ProductListStore::SORT_POPULARITY_DESC);
        self::assertEquals([$this->obElement->id - 1], array_values($obCollection->getIDList()), $sErrorMessage);
    }


    /**
     * Create  object for test
     * @param int $iCount
     */
    protected function createTestData($iCount = null)
    {
        //Create product data
        $arCreateData = $this->arCreateData;
        $arCreateData['active'] = true;
        $arCreateData['slug'] = $arCreateData['slug'].$iCount;
        $arCreateData['popularity'] = $iCount;
        $this->obElement = Product::create($arCreateData);
    }
}