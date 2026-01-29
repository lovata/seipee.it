<?php namespace Lovata\ApiSynchronization\Classes\Event;

use Lovata\ApiSynchronization\Models\ProductAlias;
use Lovata\ApiSynchronization\Models\ShippingDocumentPosition;
use Lovata\OrdersShopaholic\Models\Order;
use Lovata\OrdersShopaholic\Models\OrderPosition;
use Lovata\PropertiesShopaholic\Models\Property;
use Lovata\PropertiesShopaholic\Models\PropertyValue;
use Lovata\Shopaholic\Models\Product;

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
        $this->extendOrderModel();
        $this->extendOrderPositionModel();
    }

    /**
     * Extend Product model with product_aliases relation and getGroupedVariations method
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
        });
    }

    /**
     * Extend Order model with is_scheduled field for Seipee API Sync
     */
    protected function extendOrderModel()
    {
        Order::extend(function($model) {
            // Add is_scheduled field to fillable
            $model->addFillable([
                'is_scheduled',
            ]);

            // Add is_scheduled field to cached
            if (property_exists($model, 'cached') && is_array($model->cached)) {
                $model->cached[] = 'is_scheduled';
            }
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
