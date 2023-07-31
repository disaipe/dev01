<?php

namespace App\Models;

use App\Core\Enums\CustomReferenceContextType;
use App\Core\Reference\ReferenceModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reference extends ReferenceModel
{
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function company(): BelongsTo
    {
        if ($this->getContextType() === CustomReferenceContextType::Code) {
            return $this->belongsTo(
                Company::class,
                'company_code',
                'code'
            );
        }

        return $this->belongsTo(Company::class, 'company_id');
    }

    public function hasCompanyContext(): bool
    {
        return false;
    }

    public function getContextType(): CustomReferenceContextType
    {
        return CustomReferenceContextType::from('code');
    }

    public function scopeCompany(Builder $query, string $code): void
    {
        if ($this->hasCompanyContext()) {
            if ($this->getContextType() === CustomReferenceContextType::Code) {
                $query->where('company_code', '=', $code);
            } else {
                $query->whereHas('company', fn (Builder $q) => $q->where('code', '=', $code));
            }
        }
    }
}
