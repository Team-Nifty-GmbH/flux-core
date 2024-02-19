<?php

namespace FluxErp\Providers;

use FluxErp\Facades\Asset;
use FluxErp\Models\Currency;
use FluxErp\View\Layouts\App;
use FluxErp\View\Layouts\Printing;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\FileViewFinder;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole() || $this->app->runningUnitTests()) {
            Asset::vite(flux_path('public/build'), [
                'resources/js/app.js',
                'resources/js/alpine.js',
                'resources/js/apex-charts.js',
                'resources/css/app.css',
            ]);
        }

        /** use @extendFlux() at the end of the component, not the beginning */
        Blade::directive('extendFlux', function (string $expression) {
            $expression = trim($expression, '\'');

            // Split the expression into its components
            $parts = explode(',', $expression);

            // Trim and remove quotes from each part
            $view = trim($parts[0], ' "\'');
            $viewPaths = $parts[1] ?? false ? eval('return ' . trim($parts[1], ' "\'') . ';') : null;

            if (is_string($viewPaths)) {
                $viewPaths = [$viewPaths];
            }

            $viewPaths = array_merge($viewPaths ?? [], [flux_path('resources/views')]);

            $finder = new FileViewFinder(app('files'), $viewPaths);
            $filePath = $finder->find($view);

            return Blade::compileString(file_get_contents($filePath));
        });

        Blade::component(App::class, 'layouts.app');
        Blade::component(Printing::class, 'layouts.print');
        config([
            'livewire.layout' => 'flux::layouts.app',
        ]);

        // Register Printing views as blade components
        $views[] = __DIR__ . '/../../resources/views/printing';
        $this->loadViewsFrom($views, 'print');

        View::composer('*', function () {
            try {
                $migrated = app('migrator')->repositoryExists();
            } catch (QueryException) {
                $migrated = false;
            }

            if (
                $migrated &&
                (! $this->app->runningInConsole() || $this->app->runningUnitTests())
            ) {
                View::share(
                    'defaultCurrency',
                    Cache::remember('defaultCurrency', 60 * 60 * 24, function () {
                        return Currency::default();
                    }) ?? new Currency()
                );
            } else {
                View::share('defaultCurrency', new Currency());
            }
        });
    }
}
