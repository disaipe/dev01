<?php

namespace App\Providers;

use App\Models\Domain;
use App\Services\LdapService;
use Illuminate\Support\ServiceProvider;

class LdapConnectionProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerConnections();
    }

    private function registerConnections()
    {
        $domains = Domain::query()->where('enabled', true)->get();

        foreach ($domains as $domain) {
            LdapService::addDomainConnection($domain);
        }
    }
}
