<?php

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\Builder;

trait CompanyScope
{
    /**
     * Scope a query to only include records of a given company
     */
    public function scopeCompany(Builder $query, string $code): void
    {
        $query->where($this->companyCodeColumn ?? 'company_code', '=', $code);
    }

    public function getCompanyColumn(): ?string
    {
        return $this->companyCodeColumn;
    }
}
