<?php

namespace App\Models;

use App\Core\Reference\ReferenceModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property string name
 * @property int service_provider_id
 * @property bool is_default
 * @property ServiceProvider serviceProvider
 * @property Collection<PriceListValue> values
 */
class PriceList extends ReferenceModel
{
    protected $fillable = [
        'name',
        'service_provider_id',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(PriceListValue::class);
    }
}
