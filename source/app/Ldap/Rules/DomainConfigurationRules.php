<?php

namespace App\Ldap\Rules;

use App\Models\Domain;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Arr;
use LdapRecord\Laravel\Auth\Rule;
use LdapRecord\Models\Model as LdapRecord;

class DomainConfigurationRules implements Rule
{
    /**
     * Check if the rule passes validation.
     */
    public function passes(LdapRecord $user, ?Eloquent $model = null): bool
    {
        $connectionName = $user->getConnectionName();

        /** @var ?Domain $domain */
        $domain = Domain::query()->firstWhere('code', '=', $connectionName);

        if (is_array($domain?->filters)) {
            $filters = collect($domain->filters)
                ->map(fn ($row) => Arr::get($row, 'value'))
                ->filter();

            if ($filters->count()) {
                return $user->rawFilter($filters->toArray())->exists();
            }
        }

        return $user->exists();
    }
}
