<?php

namespace App\Providers;

use App\Models\Domain;
use App\Services\LdapService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class LdapConnectionProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerConnections();
    }

    private function registerConnections()
    {
        if (! Schema::hasTable('domains')) {
            return;
        }

        $domains = Domain::query()->where('enabled', true)->get();

        foreach ($domains as $domain) {
            LdapService::addDomainConnection($domain);
        }
    }
}
