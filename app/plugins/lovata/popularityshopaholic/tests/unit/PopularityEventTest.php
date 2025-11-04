<?php namespace Lovata\PopularityShopaholic\Tests\Unit\Component;

use Lovata\Shopaholic\Classes\Collection\ProductCollection;
use Lovata\Shopaholic\Classes\Item\ProductItem;
use Lovata\Shopaholic\Classes\Store\ProductListStore;
use Lovata\Shopaholic\Components\ProductPage;
use Lovata\Shopaholic\Models\Settings;
use Lovata\Toolbox\Tests\CommonTest;

use Lovata\Shopaholic\Models\Offer;
use Lovata\Shopaholic\Models\Product;
use System\Classes\PluginManager;

/**
 * Class PopularityEventTest
 * @package Lovata\PopularityShopaholic\Tests\Unit\Component
 * @author Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 *
 * @mixin \PHPUnit\Framework\Assert
 */
class PopularityEventTest extends CommonTest
{
    /** @var Product */
    protected $obProduct;

    /** @var Offer */
    protected $obOffer;

    protected $arProductData = [
        'active' => true,
        'name'   => 'name',
        'slug'   => 'slug',
    ];

    protected $arOfferData = [
        'active' => true,
        'name'   => 'name',
    ];

    /**
     * Test popularity events
     */
    public function testPopularityEvents()
    {
        $this->createTestData();

        $this->checkProductPageEvent();

        if (PluginManager::instance()->hasPlugin('Lovata.OrdersShopaholic')) {
            $this->checkCartAddEvent();
            $this->checkOrderAddEvent();
        }
    }

    /**
     * Checking shopaholic.product.open event
     */
    protected function checkProductPageEvent()
    {
        Settings::set('popularity_open_product', 1);
        
        $sMessage = 'Event shopaholic.product.open not work';

        $obProductList = ProductCollection::make()->sort(ProductListStore::SORT_POPULARITY_DESC);

        self::assertEquals(2, $obProductList->count(), $sMessage);
        
        /** @var ProductItem $obProductItem */
        $obProductItem = $obProductList->last();
        self::assertEquals($this->obProduct->id, $obProductItem->id, $sMessage);

        $obComponent = new ProductPage(null, ['slug' => 'slug1']);
        $obComponent->init();

        $obProductList = ProductCollection::make()->sort(ProductListStore::SORT_POPULARITY_DESC);

        /** @var ProductItem $obProductItem */
        $obProductItem = $obProductList->first();
        self::assertEquals($this->obProduct->id, $obProductItem->id, $sMessage);
        
        $obProduct = Product::find($this->obProduct->id);
        self::assertEquals(1, $obProduct->popularity, $sMessage);
        
        $obProduct->popularity = 0;
        $obProduct->save();
    }

    /**
     * Checking shopaholic.cart.add event
     */
    protected function checkCartAddEvent()
    {
        Settings::set('popularity_cart_add', 2);

        $sMessage = 'Event shopaholic.cart.add not work';

        $obProductList = ProductCollection::make()->sort(ProductListStore::SORT_POPULARITY_DESC);

        self::assertEquals(2, $obProductList->count(), $sMessage);

        /** @var ProductItem $obProductItem */
        $obProductItem = $obProductList->last();
        self::assertEquals($this->obProduct->id, $obProductItem->id, $sMessage);

        $arOfferList = [
            [
                'offer_id' => $this->obOffer->id,
                'quantity' => 1,
            ],
        ];

        /** @var \Lovata\OrdersShopaholic\Classes\Processor\CartProcessor $obCartProcessor */
        $obCartProcessor = \Lovata\OrdersShopaholic\Classes\Processor\CartProcessor::instance();
        $obCartProcessor->add($arOfferList, \Lovata\OrdersShopaholic\Classes\Processor\OfferCartPositionProcessor::class);

        $obProductList = ProductCollection::make()->sort(ProductListStore::SORT_POPULARITY_DESC);

        /** @var ProductItem $obProductItem */
        $obProductItem = $obProductList->first();
        self::assertEquals($this->obProduct->id, $obProductItem->id, $sMessage);

        $obProduct = Product::find($this->obProduct->id);
        self::assertEquals(2, $obProduct->popularity, $sMessage);

        $obProduct->popularity = 0;
        $obProduct->save();
    }

    /**
     * Checking shopaholic.order.created event
     */
    protected function checkOrderAddEvent()
    {
        Settings::set('popularity_order_create', 5);

        $sMessage = 'Event shopaholic.order.created not work';

        $obProductList = ProductCollection::make()->sort(ProductListStore::SORT_POPULARITY_DESC);

        self::assertEquals(2, $obProductList->count(), $sMessage);

        /** @var ProductItem $obProductItem */
        $obProductItem = $obProductList->last();
        self::assertEquals($this->obProduct->id, $obProductItem->id, $sMessage);

        $arOfferList = [
            [
                'offer_id' => $this->obOffer->id,
                'quantity' => 1,
            ],
        ];

        /** @var \Lovata\OrdersShopaholic\Classes\Processor\CartProcessor $obCartProcessor */
        $obCartProcessor = \Lovata\OrdersShopaholic\Classes\Processor\CartProcessor::instance();
        $obCartProcessor->add($arOfferList, \Lovata\OrdersShopaholic\Classes\Processor\OfferCartPositionProcessor::class);

        $arUserData = [
            'email'       => 'email@email.com',
            'name'        => 'name',
            'last_name'   => 'last_name',
            'middle_name' => 'middle_name',
            'phone_list'  => ['123', '321'],
        ];

        $obComponent = new \Lovata\OrdersShopaholic\Components\MakeOrder();
        $obComponent->init();
        $obComponent->create([], $arUserData);
        
        $obProductList = ProductCollection::make()->sort(ProductListStore::SORT_POPULARITY_DESC);

        /** @var ProductItem $obProductItem */
        $obProductItem = $obProductList->first();
        self::assertEquals($this->obProduct->id, $obProductItem->id, $sMessage);

        $obProduct = Product::find($this->obProduct->id);
        self::assertEquals(7, $obProduct->popularity, $sMessage);
    }

    /**
     * Create shipping type object for test
     */
    protected function createTestData()
    {
        //Create product data
        $arProductData = $this->arProductData;
        Product::create($arProductData);

        $arProductData['slug'] .= '1';
        $this->obProduct = Product::create($arProductData);

        //Create offer data
        $arOfferData = $this->arOfferData;
        $arOfferData['product_id'] = $this->obProduct->id;
        $this->obOffer = Offer::create($arOfferData);
    }
}
