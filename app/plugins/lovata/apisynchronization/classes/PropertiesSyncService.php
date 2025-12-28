<?php namespace Lovata\ApiSynchronization\classes;

use Illuminate\Support\Arr;
use Lovata\PropertiesShopaholic\Models\Property;
use Lovata\PropertiesShopaholic\Models\PropertyValue;
use Lovata\PropertiesShopaholic\Models\PropertyValueLink;
use Lovata\Shopaholic\Models\Product;

class PropertiesSyncService
{
    protected ApiClientService $api;

    public function __construct(ApiClientService $api)
    {
        $this->api = $api;
    }

    /**
     * Sync option groups (TipoVar) to Properties
     * @param int      $rows
     * @param int|null $maxPages
     * @return array [created=>int, updated=>int]
     */
    public function syncGroups(int $rows = 200, ?int $maxPages = null, ?int $maxItems = null): array
    {
        $created = 0;
        $updated = 0;
        $processed = 0;
        foreach ($this->api->paginate('xbtvw_B2B_TipoVar', $rows, null, $maxPages, $maxItems) as $pageData) {
            $list = Arr::get($pageData, 'result', []);
            foreach ($list as $row) {
                if ($maxItems !== null && $processed >= $maxItems) {
                    break 2;
                }
                $externalId = trim((string) ($row['Codice'] ?? ''));
                //Avoiding SIGLA FORNITORE property
                if ($externalId === '' || $externalId === 'S0005') {
                    continue;
                }
                $name = (string) ($row['Descrizione'] ?? $externalId);

                $prop = Property::where('external_id', $externalId)->first();
                if (!$prop) {
                    $prop = new Property();
                    $prop->external_id = $externalId;
                    $prop->active = true;
                    $prop->type = 'select';
                    $created++;
                } else {
                    $updated++;
                }
                $prop->name = $name;

                if (empty($prop->code)) {
                    $prop->code = $externalId;
                }

                $settings = (array) $prop->settings;
                $settings['is_translatable'] = true;
                $prop->settings = $settings;
                $prop->save();
                $processed++;
            }
        }
        return compact('created', 'updated');
    }

    /**
     * Sync option values (VarLingua) and link to properties by CodiceTipoCaratteristica
     * @param int      $rows
     * @param int|null $maxPages
     * @return array
     */
    public function syncValues(int $rows = 200, ?int $maxPages = null, ?int $maxItems = null): array
    {
        $created = 0;
        $updated = 0;
        $linked = 0;
        $skipped = 0;
        $processed = 0;
        $defaultLocale = 'it'; // default site locale for base language writes

        foreach ($this->api->paginate('xbtvw_B2B_VarLingua', $rows, null, $maxPages, $maxItems) as $pageData) {
            $list = Arr::get($pageData, 'result', []);
            foreach ($list as $row) {
                $label = $row['Descrizione'] ?? '';
                $optionCode = trim((string)($row['CodiceTipoCaratteristica'] ?? ''));
                $valueCode = trim((string)($row['CodiceCaratteristica'] ?? ''));
                if ($valueCode === '' || $label === '' || $optionCode === '') { $skipped++; $processed++; continue; }

                /** @var PropertyValue|null $pv */
                $pv = PropertyValue::where('external_id', $valueCode)->first();
                $isNew = !$pv;
                if ($isNew) {
                    $pv = new PropertyValue();
                    $pv->external_id = $valueCode;
                }

                $needsSave = false;
                if ($pv->value !== $valueCode) {
                    $pv->value = $valueCode;
                    $needsSave = true;
                }

                $transLang = $row['CodiceLingua'] ?? $row['Lingua'] ?? '';
                $locale = $this->mapLang($transLang) ?: $defaultLocale;

                $currentLabel = $pv->getAttributeTranslated('label', $locale);
                if ($currentLabel !== $label) {
                    $pv->setAttributeTranslated('label', $label, $locale);
                    $needsSave = true;
                }

                if ($isNew) {
                    $pv->save();
                    $created++;
                } elseif ($needsSave) {
                    $pv->save();
                    $updated++;
                } else {
                    $skipped++;
                }

                // Link value to property (group) if provided, count only new links
                $prop = Property::where('external_id', $optionCode)->first();
                if ($prop) {
                    $alreadyLinked = $prop->property_value()
                        ->where('lovata_properties_shopaholic_variant_link.value_id', $pv->id)
                        ->exists();
                    if (!$alreadyLinked) {
                        $prop->property_value()->syncWithoutDetaching([$pv->id]);
                        $linked++;
                    }
                }

                $processed++;
            }
        }
        return compact('created', 'updated', 'linked', 'skipped');
    }

    /**
     * Sync property values to products from VarCf endpoint
     * @param int      $rows
     * @param int|null $maxPages
     * @param int|null $maxItems
     * @return array [linked=>int, skipped=>int]
     */
    public function syncProductProperties(int $rows = 200, ?int $maxPages = null, ?int $maxItems = null): array
    {
        $linked = 0;
        $skipped = 0;
        $processed = 0;

        $where = 'CodiceCliente is null';

        foreach ($this->api->paginate('xbtvw_B2B_VarCf', $rows, $where, $maxPages, $maxItems) as $pageData) {
            $list = Arr::get($pageData, 'result', []);
            foreach ($list as $row) {
                if ($maxItems !== null && $processed >= $maxItems) {
                    break 2;
                }

                $valueCode = trim((string)($row['CodiceCaratteristica'] ?? ''));
                $productExternalId = trim((string)($row['CodiceMotoreBase'] ?? ''));

                if ($valueCode === '' || $productExternalId === '') {
                    $skipped++;
                    $processed++;
                    continue;
                }

                $propertyValue = PropertyValue::where('external_id', $valueCode)->first();
                if (!$propertyValue) {
                    $skipped++;
                    $processed++;
                    continue;
                }

                // Get the first property linked to this value
                $property = $propertyValue->property()->first();
                if (!$property || !isset($property->id)) {
                    $skipped++;
                    $processed++;
                    continue;
                }

                $product = Product::where('external_id', $productExternalId)->first();
                if (!$product || !isset($product->id)) {
                    $skipped++;
                    $processed++;
                    continue;
                }

                // Check if link already exists
                $alreadyLinked = PropertyValueLink::where([
                    'product_id' => $product->id,
                    'property_id' => $property->id,
                    'value_id' => $propertyValue->id,
                    'element_id' => $product->id,
                    'element_type' => Product::class,
                ])->exists();

                if (!$alreadyLinked) {
                    try {
                        PropertyValueLink::create([
                            'product_id' => $product->id,
                            'property_id' => $property->id,
                            'value_id' => $propertyValue->id,
                            'element_id' => $product->id,
                            'element_type' => Product::class,
                        ]);
                        $linked++;
                    } catch (\Exception $e) {
                        // Log error and skip
                        \Log::error('Failed to create PropertyValueLink: ' . $e->getMessage(), [
                            'product_id' => $product->id ?? 'null',
                            'property_id' => $property->id ?? 'null',
                            'value_id' => $propertyValue->id ?? 'null',
                        ]);
                        $skipped++;
                    }
                } else {
                    $skipped++;
                }

                $processed++;
            }
        }

        return compact('linked', 'skipped');
    }

    protected function extractLabel(array $row): string
    {
        // Per official docs, prefer 'Valore' as the translation text.
        $label = $row['Valore'] ?? '';
        if (is_string($label) && $label !== '') {
            return $label;
        }
        // Fallbacks for legacy/alternative payloads we observed earlier
        $trad = $row['Traduzione'] ?? null;
        if (is_string($trad) && $trad !== '') {
            return $trad;
        }
        $descr = $row['Descrizione'] ?? null;
        if (is_string($descr) && $descr !== '') {
            return $descr;
        }
        return '';
    }

    protected function mapLang(string $code): ?string
    {
        $map = [
            // Short ISO-like
            'IT'  => 'it', 'EN' => 'en', 'FR' => 'fr', 'DE' => 'de', 'ES' => 'es',
            // Long/three-letter variants
            'ITA' => 'it',
            'ENG' => 'en',
            'FRA' => 'fr', 'FRE' => 'fr',
            'DEU' => 'de', 'GER' => 'de',
            'ESP' => 'es', 'SPA' => 'es',
        ];
        return $map[$code] ?? null;
    }
}
