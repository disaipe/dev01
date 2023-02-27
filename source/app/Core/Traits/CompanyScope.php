<?php

namespace App\Core\Traits;

use Illuminate\Database\Eloquent\Builder;

trait CompanyScope {
    /**
     * Scope a query to only include records of a given company
     *
     * @param Builder $query
     * @param string $code
     * @return Builder
     */
    public function scopeCompany(Builder $query, string $code): Builder
    {
        return $query->where($this->companyCodeColumn ?? 'company_code', '=', $code);
    }
}
