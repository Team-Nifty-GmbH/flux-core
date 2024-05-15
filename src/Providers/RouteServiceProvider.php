<?php

namespace FluxErp\Providers;

use FluxErp\Http\Middleware\PortalMiddleware;
use FluxErp\Http\Middleware\SetAcceptHeaders;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Fortify;
use Livewire\Livewire;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/';

    public static bool $registerFluxRoutes = true;

    public static bool $registerPortalRoutes = true;

    public static bool $registerApiRoutes = true;

    public function register(): void
    {
        parent::register();

        Fortify::ignoreRoutes();
    }

    public function boot(): void
    {
        $this->mapWebRoutes();
        $this->configureRateLimiting();

        Route::pattern('id', '[0-9]+');
        Livewire::addPersistentMiddleware(PortalMiddleware::class);

        $this->routes(function () {
            if (static::$registerApiRoutes) {
                Route::prefix('api')
                    ->middleware(['throttle:api', SetAcceptHeaders::class])
                    ->namespace($this->namespace)
                    ->group(__DIR__ . '/../../routes/api.php');
            }

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(__DIR__ . '/../../routes/web.php');
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        if (static::$registerApiRoutes) {
            RateLimiter::for('api', function (Request $request) {
                return Limit::perMinute(config('flux.rate_limit'))
                    ->by(optional($request->user())->id ?: $request->ip());
            });
        }
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        if (static::$registerFluxRoutes || static::$registerPortalRoutes) {
            Authenticate::redirectUsing(fn (Request $request) => route('login', absolute: false));
        }

        // Load the subdomain routes first.
        if (static::$registerPortalRoutes) {
            Route::middleware(['web', PortalMiddleware::class])
                ->domain(config('flux.portal_domain'))
                ->group(__DIR__ . '/../../routes/frontend/portal.php');
            if (config('flux.portal_domain')) {
                Route::namespace('Laravel\Fortify\Http\Controllers')
                    ->domain(config('flux.portal_domain'))
                    ->prefix(config('fortify.prefix'))
                    ->group(__DIR__ . '/../../routes/fortify.php');
            }
        }

        // Load the default routes second.
        if (static::$registerFluxRoutes) {
            Route::middleware('web')
                ->domain(config('flux.flux_url'))
                ->group(__DIR__ . '/../../routes/frontend/web.php');
            Route::namespace('Laravel\Fortify\Http\Controllers')
                ->domain(config('flux.flux_url') ?? config('fortify.domain'))
                ->prefix(config('fortify.prefix'))
                ->group(__DIR__ . '/../../routes/fortify.php');
        }
    }
}
