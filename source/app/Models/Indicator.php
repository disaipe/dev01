<?php

namespace App\Models;

use App\Core\Indicator\IndicatorManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * @property string code
 * @property string name
 * @property array schema
 * @property bool published
 */
class Indicator extends Model
{
    protected $fillable = [
        'code',
        'name',
        'schema',
        'published',
    ];

    protected $casts = [
        'schema' => 'json',
        'published' => 'bool',
    ];

    public function asRelated(): array
    {
        /** @var IndicatorManager $indicators */
        $indicatorManager = app('indicators');

        $indicators = $indicatorManager->getIndicators();
        $values = Arr::map($indicators, fn ($indicator) => $indicator->toArray());

        return array_values($values);
    }
}
