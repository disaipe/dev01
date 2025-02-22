<?php

namespace App\Utils;

use App\Core\Indicator\IndicatorManager;
use App\Core\Reference\ReferenceEntry;
use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceManager;
use App\Core\Report\ExpressionType\QueryExpressionType;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ReferenceUtils
{
    /**
     * Returns reference fields schema
     *
     * @param  string  $name  reference name
     */
    public static function getReferenceFields(string $name): Collection
    {
        $reference = static::getReferences()->getByName($name);

        return collect($reference->getSchema());
    }

    /**
     * Returns reference fields as Filament select options
     *
     * @param  string  $name  reference name
     */
    public static function getReferenceFieldsOptions(string $name): array
    {
        return static::getReferenceFields($name)
            ->filter(fn (ReferenceFieldSchema $field) => ! $field->isHidden())
            ->mapWithKeys(fn (ReferenceFieldSchema $field, string $key) => [$key => $field->getLabel()])
            ->toArray();
    }

    /**
     * Returns reference from indicator by they code
     *
     * @param  string  $code  indicator code
     */
    public static function getIndicatorReference(string $code): ?ReferenceEntry
    {
        $indicator = static::getIndicators()->getByCode($code);

        if ($indicator->getType() instanceof QueryExpressionType) {
            $schema = $indicator->schema();
            $referenceName = Arr::get($schema, 'reference');

            if ($referenceName) {
                return static::getReferences()->getByName($referenceName);
            }
        }

        return null;
    }

    /**
     * Returns reference fields schema from indicator by they code
     *
     * @param  string  $code  indicator code
     */
    public static function getIndicatorReferenceFields(string $code): Collection
    {
        $reference = static::getIndicatorReference($code);

        if ($reference) {
            return static::getReferenceFields($reference->getName());
        }

        return collect();
    }

    /**
     * Returns reference fields as Filament select options from indicator by they code
     *
     * @param  string  $code  indicator code
     */
    public static function getIndicatorReferenceFieldsOptions(string $code): array
    {
        $reference = static::getIndicatorReference($code);

        if ($reference) {
            return static::getReferenceFieldsOptions($reference->getName());
        }

        return [];
    }

    protected static function getReferences(): ReferenceManager
    {
        /** @var ReferenceManager $references */
        $references = app('references');

        return $references;
    }

    protected static function getIndicators(): IndicatorManager
    {
        /** @var IndicatorManager $indicators */
        $indicators = app('indicators');

        return $indicators;
    }
}
