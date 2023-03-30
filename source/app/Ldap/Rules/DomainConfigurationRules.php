<?php

namespace App\Ldap\Rules;

use App\Models\Domain;
use Illuminate\Support\Arr;
use LdapRecord\Laravel\Auth\Rule;

class DomainConfigurationRules extends Rule
{
    /**
     * Check if the rule passes validation.
     */
    public function isValid(): bool
    {
        $connectionName = $this->user->getConnectionName();

        /** @var ?Domain $domain */
        $domain = Domain::query()->firstWhere('code', '=', $connectionName);

        if (is_array($domain?->filters)) {
            $filters = collect($domain->filters)
                ->map(fn ($row) => Arr::get($row, 'value'))
                ->filter();

            if ($filters->count()) {
                return $this->user->rawFilter($filters->toArray())->exists();
            }
        }

        return $this->user->exists();
    }
}
