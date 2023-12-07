<?php

namespace App\Models;

use App\Core\Reference\ReferenceModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property string name
 * @property int service_provider_id
 * @property bool is_default
 * @property ServiceProvider service_provider
 * @property Collection<Company> companies
 * @property Collection<PriceListValue> values
 * @method
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

    protected $appends = [
        'companies_keys'
    ];

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'price_list_companies');
    }

    public function service_provider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(PriceListValue::class);
    }

    public function companiesKeys(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->companies->pluck('id')->toArray(),
            set: fn ($v) => $this->companies()->sync($v)
        );
    }
}
