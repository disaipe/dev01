<?php

namespace App\Models;

use App\Core\Indicator\IndicatorManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @property string code
 * @property string type
 * @property string name
 * @property array schema
 * @property bool published
 * @property ?IndicatorGroup group
 * @property Service[] services
 */
class Indicator extends Model
{
    protected $fillable = [
        'code',
        'type',
        'name',
        'schema',
        'published',
        'indicator_group_id',
    ];

    protected $casts = [
        'schema' => 'json',
        'published' => 'bool',
    ];

    public function group(): BelongsTo {
        return $this->belongsTo(IndicatorGroup::class, 'indicator_group_id');
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'indicator_code', 'code');
    }

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

    protected static function boot(): void
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
