<?php

namespace App\Models\Scopes;

use App\Core\ClientCompanyContext;
use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        /** @var ClientCompanyContext $context */
        $context = app(ClientCompanyContext::class);

        if ($context->isHasCompanyContext()) {
            if (get_class($model) === Company::class) {
                $builder->whereIn('code', $context->getClientCompaniesCodes());
            } else {
                if (method_exists($model, 'scopeCompany')) {
                    $builder->company($context->getCompanyContextCode());
                }
            }
        }
    }
}
