<?php

namespace App\Models;

use App\Core\Indicator\IndicatorManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
        $values = Arr::sort($values, fn ($indicator) => Arr::get($indicator, 'name'));

        return array_values($values);
    }

    public function scopeEnabled(Builder $query): void
    {
        $query->where('published', 1);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Indicator $query) {
            // Generate code if not set
            if (! isset($query->code)) {
                $query->code = 'INDICATOR_'.Str::random('6');
            }
        });
    }
}
