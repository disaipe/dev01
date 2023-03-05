<?php

namespace App\Providers;

use App\Core\Indicator\IndicatorManager;
use App\Directives;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerDirectives();

        $this->app->singleton('indicators', fn () => new IndicatorManager());
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    private function registerDirectives()
    {
        Blade::directive('vue', [Directives::class, 'vue']);
    }
}
