<?php namespace Lovata\PropertiesShopaholic\Classes\Helper;

use DB;
use System\Classes\PluginManager;
use RainLab\Translate\Classes\Locale;

use Lovata\Shopaholic\Models\Offer;
use Lovata\Shopaholic\Models\Product;
use Lovata\PropertiesShopaholic\Models\Property;
use Lovata\PropertiesShopaholic\Models\PropertyValue;
use Lovata\PropertiesShopaholic\Models\PropertyValueLink;

/**
 * Class CommonPropertyHelper
 * @package Lovata\PropertiesShopaholic\Classes\Helper
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class CommonPropertyHelper
{
    /** @var string */
    protected $sModelClass;

    /** @var int */
    protected $iProductID;

    /** @var \Lovata\Shopaholic\Models\Offer|\Lovata\Shopaholic\Models\Product */
    protected $obElement;

    /** @var \October\Rain\Database\Collection|\Lovata\PropertiesShopaholic\Models\PropertyValueLink[] */
    protected $obValueLinkList;

    /** @var array */
    protected $arValueList;

    /** @var array */
    protected $arProcessedLinkList = [];

    /** @var bool */
    protected $bHasTranslatePlugin = false;

    /** @var null|string */
    protected $sDefaultLocale = null;

    /** @var null|string */
    protected $sContextLocale = null;

    /** @var bool */
    protected $bIsDefaultLocale = false;

    /**
     * ProductPropertyHelper constructor.
     * @param Product|Offer $obElement
     */
    public function __construct($obElement)
    {
        if (empty($obElement) || (!$obElement instanceof Product && !$obElement instanceof Offer)) {
            return;
        }

        $this->obElement = $obElement;

        $this->sModelClass = get_class($this->obElement);

        $this->iProductID = $obElement instanceof Product ? $this->obElement->id : $this->obElement->product_id;

        //Get property values
        $this->obValueLinkList = PropertyValueLink::with('value', 'property')->getByElementType($this->sModelClass)
            ->getByElementID($this->obElement->id)
            ->get();

        $this->setTranslateSettings();
    }

    /**
     * Set properties (backend)
     * @param array $arValueList
     * @@throws \Exception
     */
    public function setPropertyAttribute($arValueList)
    {
        $this->arValueList = $arValueList;
        $this->processValueList();
        $this->removeOldValue();
    }

    /**
     * Get property values (backend)
     * @return array
     * @throws \Exception
     */
    public function getPropertyAttribute() : array
    {
        $arResult = [];
        if (empty($this->obElement) || $this->obValueLinkList->isEmpty()) {
            return $arResult;
        }

        foreach ($this->obValueLinkList as $obValueLink) {

            //Get property object
            $obProperty = $obValueLink->property;
            $obValue = $obValueLink->value;
            if (empty($obValue) || empty($obProperty)) {
                $obValueLink->delete();
                continue;
            }

            if ($obProperty->type == Property::TYPE_CHECKBOX) {
                if (!isset($arResult[$obProperty->id])) {
                    $arResult[$obProperty->id] = [];
                }

                $arResult[$obProperty->id][] = $obValue->value;
            } else {
                $arResult[$obProperty->id] = $obValue->value;
            }
        }

        return $arResult;
    }

    public function setContext($sContext): void
    {
        $this->sContextLocale = $sContext;
        $this->bIsDefaultLocale = ($this->sDefaultLocale === $this->sContextLocale);
    }

    /**
     * Process array with property values. Save/create/update/remove property values.
     */
    protected function processValueList()
    {
        if (empty($this->arValueList) || !is_array($this->arValueList)) {
            return;
        }

        //Process property value list
        foreach ($this->arValueList as $iPropertyID => $sValue) {

            //Get property object
            $obProperty = Property::find($iPropertyID);

            //Check value
            if (empty($obProperty) || !PropertyValue::hasValue($sValue) || ($obProperty->type == Property::TYPE_CHECKBOX && empty($sValue))) {
                continue;
            }

            //If value is array, then we need to process value list
            if (is_array($sValue)) {
                foreach ($sValue as $sSingleValue) {
                    if (!PropertyValue::hasValue($sSingleValue)) {
                        continue;
                    }

                    $this->processPropertyValue($iPropertyID, $sSingleValue);
                }
            } else {
                $this->processPropertyValue($iPropertyID, $sValue);
            }
        }
    }

    /**
     * Remove old property value links
     * @throws \Exception
     */
    protected function removeOldValue()
    {
        if ($this->obValueLinkList->isEmpty() || $this->sDefaultLocale !== $this->sContextLocale) {
            return;
        }

        foreach ($this->obValueLinkList as $obPropertyValueLink) {
            if (in_array($obPropertyValueLink->id, $this->arProcessedLinkList)) {
                continue;
            }

            $obPropertyValueLink->delete();
        }
    }

    /**
     * @param int    $iPropertyID
     * @param string $sValue
     */
    protected function processPropertyValue($iPropertyID, $sValue)
    {
        $obProperty = Property::find($iPropertyID);
        if (empty($obProperty)) {
            return;
        }

        //Get property value object
        $obPropertyValue = $this->getValueObject($sValue, $obProperty);
        if (empty($obPropertyValue)) {
            return;
        }

        //Find property value link object
        $obPropertyValueLink = $this->findPropertyLink($iPropertyID);
        if (empty($obPropertyValueLink)) {
            $obPropertyValueLink = PropertyValueLink::create([
                'value_id'     => $obPropertyValue->id,
                'property_id'  => $iPropertyID,
                'product_id'   => $this->iProductID,
                'element_id'   => $this->obElement->id,
                'element_type' => $this->sModelClass,
            ]);

        } elseif ($obPropertyValueLink->value_id != $obPropertyValue->id) {
            $obPropertyValueLink->value_id = $obPropertyValue->id;
            $obPropertyValueLink->save();
        }

        $arTypeList = [
            Property::TYPE_SELECT,
            Property::TYPE_CHECKBOX,
            Property::TYPE_RADIO,
            Property::TYPE_BALLOON,
            Property::TYPE_TAG_LIST,
        ];
        if (in_array($obProperty->type, $arTypeList)) {
            if (!DB::table('lovata_properties_shopaholic_variant_link')->where('property_id', $obProperty->id)->where('value_id', $obPropertyValue->id)->first()) {
                $obProperty->property_value()->attach($obPropertyValue->id);
            }
        } else {
            $obProperty->property_value()->detach();
        }

        $this->arProcessedLinkList[] = $obPropertyValueLink->id;
    }

    /**
     * Get property value object by slug value
     * @param string $sValue
     * @param Property $obProperty
     * @return PropertyValue
     * @throws \Exception
     */
    protected function getValueObject($sValue, $obProperty)
    {
        if (!PropertyValue::hasValue($sValue)) {
            return null;
        }

        /** @var \Lovata\PropertiesShopaholic\Models\PropertyValueLink|null $obPropertyValueLink */
        $obPropertyValueLink = $this->obValueLinkList->where('property_id', $obProperty->id)->whereNotIn('id', $this->arProcessedLinkList)->first();
        $obSavedPropertyValue = !empty($obPropertyValueLink) ? $obPropertyValueLink->value : null;

        if ($this->bHasTranslatePlugin && !$this->bIsDefaultLocale) {
            $sSavedValue = !empty($obSavedPropertyValue)
                ? $obSavedPropertyValue->getAttributeTranslated('value', $this->sContextLocale)
                : null;
            $obPropertyValue = PropertyValue::transWhere('value', $sValue, $this->sContextLocale)->first();
            $obPropertyValue = !empty($obPropertyValue) ? $obPropertyValue : PropertyValue::getByValue($sValue)->first();
        } else {
            $sSavedValue = !empty($obSavedPropertyValue) ? $obSavedPropertyValue->value : null;
            $obPropertyValue = PropertyValue::getByValue($sValue)->first();
        }

        if ($sSavedValue === $sValue) {
            return $obSavedPropertyValue;
        }

        if (!empty($obPropertyValue)) {
            if ($this->bHasTranslatePlugin && !$this->bIsDefaultLocale) {
                $obPropertyValue->setAttributeTranslated('value', $sValue, $this->sContextLocale);
                $obPropertyValue->save();
            }

            return $obPropertyValue;
        }

        try {
            if (!empty($obSavedPropertyValue) && $this->bHasTranslatePlugin && !$this->bIsDefaultLocale) {
                $obSavedPropertyValue->setAttributeTranslated('value', $sValue, $this->sContextLocale);
                $obSavedPropertyValue->save();

                return $obSavedPropertyValue;
            } else {
                return PropertyValue::create(['value' => $sValue]);
            }
        } catch (\Exception $obException) {
            throw new \Exception($obException);
        }
    }

    /**
     * Find property link by property ID
     * @param int $iPropertyID
     * @return null|PropertyValueLink
     */
    protected function findPropertyLink($iPropertyID)
    {
        if ($this->obValueLinkList->isEmpty()) {
            return null;
        }

        foreach ($this->obValueLinkList as $obPropertyValueLink) {
            if ($obPropertyValueLink->property_id != $iPropertyID || in_array($obPropertyValueLink->id, $this->arProcessedLinkList)) {
                continue;
            }

            return $obPropertyValueLink;
        }

        return null;
    }

    /**
     * Set translate settings
     * @return void
     */
    protected function setTranslateSettings()
    {
        if (!PluginManager::instance()->hasPlugin('RainLab.Translate')) {
            return;
        }

        $this->bHasTranslatePlugin = true;
        $this->sDefaultLocale = Locale::getDefaultSiteLocale();
        $this->setContext(Locale::getSiteLocaleFromContext());
    }
}
