<?php

namespace App\Providers;

use App\Core\Reference\ReferenceManager;
use App\Http\Middleware\DenyClientAccess;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    public function register(): void
    {
        parent::register();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('web')
                ->prefix('api/web')
                ->group(base_path('routes/ajax.php'));
        });

        Route::macro('reference', function (string $referenceClass) {
            /** @var ReferenceManager $references */
            $references = app('references');
            $references->register($referenceClass);
        });

        Route::macro('references', function () {
            /** @var ReferenceManager $references */
            $references = app('references');

            foreach ($references->getReferences() as $reference) {
                $prefix = $reference->getPrefix();
                $controller = $reference->controller();

                Route::prefix($prefix)->group(function () use ($controller) {
                    Route::post('', $controller->list(...));

                    Route::middleware(DenyClientAccess::class)->group(function () use ($controller) {
                        Route::post('update', $controller->push(...));
                        Route::post('remove', $controller->remove(...));
                    });

                    Route::post('export', $controller->export(...));
                    Route::get('schema', $controller->schema(...));
                    Route::get('history/{record}', $controller->history(...));
                    Route::post('related', $controller->related(...));
                });
            }
        });
    }

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
