<?php

namespace App\Providers;

use App\Core\HasManySyncMacro;
use App\Core\Indicator\IndicatorManager;
use App\Directives;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
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
        $this->app->singleton('indicators', fn () => new IndicatorManager());
    }
}
