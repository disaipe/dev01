<?php

namespace App\Providers;

use App\Core\ClientCompanyContext;
use App\Core\HasManySyncMacro;
use App\Core\Indicator\IndicatorManager;
use App\Core\Reference\ReferenceManager;
use App\Core\Report\Expression\ExpressionManager;
use App\Directives;
use App\Filament\LogoutResponse;
use App\Reference\IndicatorReference;
use App\Services\DashboardMenuService;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerMacros();
        $this->registerDirectives();
        $this->registerSingletons();
        $this->registerFixedSqlServerConnector();

        $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /** @var ReferenceManager $references */
        $references = $this->app->make('references');

        $references->register(IndicatorReference::class);
    }

    private function registerMacros(): void
    {
        HasManySyncMacro::make();
    }

    private function registerDirectives(): void
    {
        Blade::directive('vue', [Directives::class, 'vue']);
    }

    private function registerSingletons(): void
    {
        $this->app->singleton('references', fn () => new ReferenceManager());
        $this->app->singleton('expressions', fn () => new ExpressionManager());
        $this->app->singleton('indicators', fn () => new IndicatorManager());
        $this->app->singleton('menu', fn () => new DashboardMenuService());
        $this->app->singleton(ClientCompanyContext::class, fn () => new ClientCompanyContext());
    }

    private function registerFixedSqlServerConnector(): void
    {
        $this->app->bind('db.connector.sqlsrv', \App\Database\SqlServerConnector::class);
    }
}
