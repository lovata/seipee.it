<?php namespace Lovata\MightySeo\Classes\Event;

use Arr;
use Backend\Classes\Controller;

use Lovata\MightySeo\Models\SeoParam;
use Lovata\MightySeo\Models\SeoTemplate;
use Lovata\MightySeo\Classes\Item\SeoParamItem;
use Lovata\MightySeo\Classes\Helper\SeoConfigHelper;
use Lovata\MightySeo\Models\Settings;
use Tailor\Models\EntryRecord;
use Tailor\Models\GlobalRecord;

/**
 * Class ExtendModel
 * @package Lovata\MightySeo\Classes\Event
 * @author  Andrey Kharanenka, a.khoronenko@lovata.com, LOVATA Group
 */
class ExtendModel
{
    /**
     * Add listeners
     * @param \Illuminate\Events\Dispatcher $obEvent
     */
    public function subscribe($obEvent)
    {
        //Get item class list
        $arItemClassList = SeoConfigHelper::getItemClassList();
        if (!empty($arItemClassList)) {
            foreach ($arItemClassList as $sClassName) {
                $this->extendItemClass($sClassName);
            }
        }

        $arClassList = SeoConfigHelper::getModelClassList();
        if (empty($arClassList)) {
            return;
        }

        foreach ($arClassList as $sModelName) {
            $this->addRelationConfigToModel($sModelName);
        }

        $this->extendBackendControllers();
        $this->extendBackendFields($obEvent, $arClassList);

    }

    /**
     * Add relation config to model with SeoTemplate model
     * @param string $sModelName
     */
    protected function addRelationConfigToModel($sModelName)
    {
        $sModelName::extend(function ($obModel) {
            /** @var \Model $obModel */
            $obModel->morphMany['seo_template'] = [
                SeoTemplate::class,
                'name' => 'external',
            ];

            /** @var \Model $obModel */
            $obModel->morphOne['seo_container'] = [
                SeoParam::class,
                'name' => 'external',
            ];

            $obModel->addDynamicMethod('getSeoParamIdAttribute', function () use ($obModel) {
                //Get seo param model
                $obSeoParam = $this->findSeoParam($obModel);
                if (empty($obSeoParam)) {
                    return null;
                }

                return $obSeoParam->id;
            });

            $obModel->addDynamicMethod('getSeoParamAttribute', function () use ($obModel) {
                $obSeoParamItem = SeoParamItem::make($obModel->seo_param_id);

                return $obSeoParamItem;
            });

            $obModel->addDynamicMethod('setSeoValueAttribute', function ($arValue) use ($obModel) {
                //Get seo param model
                $obSeoParam = $this->findSeoParam($obModel);
                if (empty($obSeoParam)) {
                    $obSeoParam = new SeoParam();
                    $obSeoParam->external_id = $this->getExternalID($obModel);
                    $obSeoParam->external_type = get_class($obModel);
                }

                $obSeoParam->fill($arValue);
                $obSeoParam->save();
            });

            $obModel->addDynamicMethod('getSeoValueAttribute', function () use ($obModel) {
                return $this->findSeoParam($obModel);
            });

            if (method_exists($obModel, 'addCachedField')) {
                $obModel->addCachedField('seo_param_id');
            }
        });
    }

    /**
     * @param \Model $obentity
     * @return void
     */
    protected function findSeoParam($obEntity)
    {
        $obSeoContainer = $obEntity->seo_container;
        if (empty($obSeoContainer)) {
            $obSeoContainer = SeoParam::where('external_type', get_class($obEntity))->where('external_id', $this->getExternalID($obEntity))->first();
        }

        return $obSeoContainer;
    }

    /**
     * @param \Model $obEntity
     * @return string|int|null
     */
    protected function getExternalID($obEntity)
    {
        if ($obEntity instanceof EntryRecord) {
            return $obEntity->blueprint?->handle.'_'.$obEntity->id;
        }

        return $obEntity->getKey();
    }

    /**
     * Add SEO fields to backend forms
     * @param \Illuminate\Events\Dispatcher $obEvent
     * @param                               $arModelList
     */
    protected function extendBackendFields($obEvent, $arModelList)
    {
        $obEvent->listen('backend.form.extendFields', function ($obWidget) use ($arModelList) {

            /** @var \Backend\Widgets\Form $obWidget */

            $bCheckContext = $obWidget->context == 'update' || ($obWidget->context == 'create' && $obWidget->model instanceof GlobalRecord);
            if (empty($arModelList) || $obWidget->isNested || !$bCheckContext || preg_match('%PivotForm$%', $obWidget->alias)) {
                return;
            }

            //Get model class name
            $sModelClassName = get_class($obWidget->model);
            if (!in_array($sModelClassName, $arModelList)) {
                return;
            }

            if (in_array($sModelClassName, [EntryRecord::class, GlobalRecord::class])) {
                $arBlueprintList = (array) Settings::get('enabled_blueprint_entry');
                $obModel = $obWidget->model;
                $sHandle = !empty($obModel) ? $obModel->blueprint?->handle : null;
                if (!in_array($sHandle, $arBlueprintList)) {
                    return;
                }
            }

            $sTabType = $this->getTabType($sModelClassName);
            $sTabMethod = $sTabType === 'secondary' ? 'addSecondaryTabFields' : 'addTabFields';

            $obWidget->$sTabMethod([
                'seo_value' => [
                    'type'  => 'nestedform',
                    'label' => 'lovata.mightyseo::lang.tab.seo',
                    'tab'   => 'lovata.mightyseo::lang.tab.seo',
                    'form'  => '$/lovata/mightyseo/models/seoparam/default_relation_fields.yaml',
                ],
            ]);
            $obWidget->$sTabMethod([
                'seo_template' => [
                    'tab'  => 'lovata.mightyseo::lang.tab.seo_template',
                    'type' => 'partial',
                    'path' => '$/lovata/mightyseo/views/seo_template.htm',
                ],
            ]);
        });
    }

    protected function getTabType($sModelClassName)
    {
        $arConfigList = SeoConfigHelper::getSeoModelsConfig();
        if (empty($arConfigList)) {
            return 'main';
        }

        foreach ($arConfigList as $mConfig) {
            $sClassName = is_array($mConfig) ? $mConfig['model'] : $mConfig;
            if ($sClassName != $sModelClassName) {
                continue;
            }

            return is_array($mConfig) ? Arr::get($mConfig, 'tab', 'main') : 'main';
        }

        return 'main';
    }

    /**
     * Add relation config to backend controllers
     */
    protected function extendBackendControllers()
    {
        Controller::extend(function ($obController) {

            /** @var Controller $obController */
            if (empty($obController->implement)) {
                $obController->implement = [];
            }

            //Extend controller
            if (!in_array('Backend.Behaviors.RelationController', $obController->implement) && !in_array('Backend\Behaviors\RelationController', $obController->implement)) {
                $obController->implement[] = 'Backend.Behaviors.RelationController';
            }

            if (!isset($obController->relationConfig)) {
                $obController->addDynamicProperty('relationConfig');
            }

            // Splice in configuration safely
            $sConfigPath = '$/lovata/mightyseo/config/config_relation.yaml';

            $obController->relationConfig = $obController->mergeConfig(
                $obController->relationConfig,
                $sConfigPath
            );
        });
    }

    /**
     * Extend item class name
     * @param string $sClassName
     */
    protected function extendItemClass($sClassName)
    {
        $sClassName::extend(function ($obItem) {
            /** @var \Lovata\Toolbox\Classes\Item\ElementItem $obItem */
            $obItem->arRelationList['seo_param'] = [
                'class' => SeoParamItem::class,
                'field' => 'seo_param_id',
            ];
        });
    }
}
