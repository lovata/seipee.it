<?php namespace Lovata\ApiSynchronization\Classes\Event;

use Lovata\ApiSynchronization\Models\ProductAlias;
use Lovata\ApiSynchronization\Models\ShippingDocument;
use Lovata\ApiSynchronization\Models\ShippingDocumentPosition;
use Lovata\Buddies\Models\User;
use Lovata\OrdersShopaholic\Models\Order;
use Lovata\OrdersShopaholic\Models\OrderPosition;
use Lovata\PropertiesShopaholic\Models\Property;
use Lovata\PropertiesShopaholic\Models\PropertyValue;
use Lovata\PropertiesShopaholic\Models\PropertySet;
use Lovata\Shopaholic\Classes\Item\ProductItem;
use Lovata\Shopaholic\Models\Product;
use Lovata\Shopaholic\Models\Offer;
use Cache;

/**
 * Class ExtendModelsHandler
 * @package Lovata\ApiSynchronization\Classes\Event
 * @author  Seipee
 *
 * Extends Product and Order models with Seipee-specific fields and methods
 */
class ExtendModelsHandler
{
    /**
     * Subscribe to events
     */
    public function subscribe()
    {
        $this->extendProductModel();
        $this->extendPropertyModel();
        $this->extendOfferModel();
        $this->extendUserModel();
        $this->extendOrderModel();
        $this->extendOrderPositionModel();
    }

    /**
     * Extend Product model with product_aliases relation, getGroupedVariations and getCustomVariants methods
     */
    protected function extendProductModel()
    {
        Product::extend(function($model) {
            // Add product_aliases relation
            $model->hasMany['product_aliases'] = [ProductAlias::class];

            // Add getGroupedVariations method
            $model->addDynamicMethod('getGroupedVariations', function() use ($model) {
                $pivotRecords = \DB::table('lovata_shopaholic_product_variation_properties')
                    ->where('product_id', $model->id)
                    ->get();

                if ($pivotRecords->isEmpty()) {
                    return [];
                }

                $propertyIds = $pivotRecords->pluck('property_id')->unique();
                $valueIds = $pivotRecords->pluck('value_id')->unique();

                $properties = Property::whereIn('id', $propertyIds)
                    ->get()
                    ->keyBy('id');

                $values = PropertyValue::whereIn('id', $valueIds)
                    ->get()
                    ->keyBy('id');

                $result = [];

                foreach ($pivotRecords as $pivot) {
                    $propertyId = $pivot->property_id;
                    $valueId = $pivot->value_id;

                    $property = $properties->get($propertyId);
                    $value = $values->get($valueId);

                    if (!$property || !$value) {
                        continue;
                    }

                    if (!isset($result[$propertyId])) {
                        $result[$propertyId] = [
                            'property_id'   => $property->id,
                            'property_name' => $property->name,
                            'property_code' => $property->code ?? $property->external_id,
                            'values'        => [],
                        ];
                    }

                    $result[$propertyId]['values'][$value->id] = [
                        'id'    => $value->id,
                        'value' => $value->value,
                        'code'  => $value->slug ?? $value->external_id,
                        'label' => $value->label,
                    ];
                }

                foreach ($result as &$property) {
                    $property['values'] = array_values($property['values']);
                }

                return array_values($result);
            });

            // Add getCustomVariants method with caching
            $model->addDynamicMethod('getCustomVariants', function() use ($model) {
                $cacheKey = 'product_custom_variants';

                return Cache::remember($cacheKey, 129600, function() {
                    $propertySet = PropertySet::with('product_property.property_value')
                        ->where('name', 'custom')
                        ->first();

                    if (!$propertySet || !$propertySet->product_property) {
                        return collect([]);
                    }

                    $properties = $propertySet->product_property->map(function ($item) {
                        $item['variants'] = $item->getPropertyVariants();
                        return $item;
                    });

                    return $properties;
                });
            });
        });

        ProductItem::extend(function (ProductItem $product) {
            $product->addDynamicMethod('getCustomVariants', function () use ($product) {
                $cacheKey = 'product_custom_variants';

                return Cache::remember($cacheKey, 129600, function() {
                    $propertySet = PropertySet::with('product_property.property_value')
                        ->where('name', 'custom')
                        ->first();

                    if (!$propertySet || !$propertySet->product_property) {
                        return collect([]);
                    }

                    $properties = $propertySet->product_property->map(function ($item) {
                        $item['variants'] = $item->getPropertyVariants();
                        return $item;
                    });

                    return $properties;
                });
            });
        });
    }

    /**
     * Extend Property model with hasValue method
     */
    protected function extendPropertyModel()
    {
        Property::extend(function($model) {
            // Add hasValue method to check if property has values
            $model->addDynamicMethod('hasValue', function() use ($model) {
                $obPropertyValues = $model->property_value;
                return !empty($obPropertyValues) && $obPropertyValues->isNotEmpty();
            });
        });
    }

    /**
     * Extend Offer model with warehouse fields for Seipee API Sync
     */
    protected function extendOfferModel()
    {
        Offer::extend(function($model) {
            // Add warehouse fields to fillable
            $model->addFillable([
                'warehouse_internal',
                'warehouse_external',
            ]);

            // Add warehouse fields to cached (if cached is used)
            if (property_exists($model, 'cached') && is_array($model->cached)) {
                $model->cached[] = 'warehouse_internal';
                $model->cached[] = 'warehouse_external';
            }

            // Add casts for warehouse fields
            $model->addCasts([
                'warehouse_internal' => 'integer',
                'warehouse_external' => 'integer',
            ]);
        });
    }

    /**
     * Extend User model with Seipee-specific fields and relations
     */
    protected function extendUserModel()
    {
        User::extend(function($model) {
            // Add Seipee-specific fields to fillable
            $model->addFillable([
                'erp_user_code',
                'external_id',
                'alternate_destination_code',
                'payment',
                'shipping',
            ]);

            // Add Seipee-specific fields to cached
            if (property_exists($model, 'cached') && is_array($model->cached)) {
                $model->cached[] = 'erp_user_code';
                $model->cached[] = 'external_id';
            }

            // Add product_aliases relation
            $model->hasMany['product_aliases'] = [ProductAlias::class];
        });
    }

    /**
     * Extend Order model with Seipee-specific fields and relations
     */
    protected function extendOrderModel()
    {
        Order::extend(function($model) {
            // Add Seipee-specific fields to fillable
            $model->addFillable([
                'is_scheduled',
                'seipee_order_id',
                'payment_type',
                'delivery_date',
                'is_delivered',
                'items_count',
            ]);

            // Add Seipee-specific fields to cached
            if (property_exists($model, 'cached') && is_array($model->cached)) {
                $model->cached[] = 'is_scheduled';
                $model->cached[] = 'seipee_order_id';
                $model->cached[] = 'payment_type';
                $model->cached[] = 'delivery_date';
                $model->cached[] = 'is_delivered';
                $model->cached[] = 'items_count';
            }

            // Add delivery_date to dates array
            if (property_exists($model, 'dates') && is_array($model->dates)) {
                $model->dates[] = 'delivery_date';
            }

            // Add shipping_documents relation
            $model->addDynamicMethod('shipping_documents', function() use ($model) {
                return ShippingDocument::where('seipee_order_id', $model->seipee_order_id)->get();
            });
        });
    }

    /**
     * Extend OrderPosition model with shipping_document_positions relation
     */
    protected function extendOrderPositionModel()
    {
        OrderPosition::extend(function($model) {
            // Add shipping_document_positions relation
            $model->hasMany['shipping_document_positions'] = [ShippingDocumentPosition::class, 'key' => 'order_position_id'];
        });
    }
}
