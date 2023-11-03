<?php

namespace App\Models;

use App\Core\Reference\ReferenceModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int company_id
 * @property int service_provider_id
 * @property string number
 * @property Carbon date
 * @property string description
 * @property bool is_actual
 * @property Company company
 * @property ServiceProvider service_provider
 */
class Contract extends ReferenceModel
{
    protected $fillable = [
        'company_id',
        'service_provider_id',
        'number',
        'date',
        'description',
        'is_actual',
    ];

    protected $casts = [
        'date' => 'date',
        'is_actual' => 'bool',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function service_provider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }

    public function scopeCompany(Builder $query, string $code): void
    {
        $query->whereHas(
            'company',
            fn (Builder $company) => $company->where('code', '=', $code)
        );
    }
}
