<?php

namespace FluxErp\Providers;

use Composer\Autoload\ClassLoader;
use Composer\InstalledVersions;
use FluxErp\Facades\Asset;
use FluxErp\Models\Currency;
use FluxErp\View\Layouts\App;
use FluxErp\View\Layouts\Printing;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\FileViewFinder;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (
            (! $this->app->runningInConsole() || $this->app->runningUnitTests())
            && file_exists(public_path('build/manifest.json'))
        ) {
            // get the real path for the flux package root folder
            Asset::vite(
                public_path('build'),
                [
                    static::getRealPackageAssetPath(
                        'resources/css/app.css',
                        'team-nifty-gmbh/flux-erp'
                    ),
                    static::getRealPackageAssetPath(
                        'resources/js/app.js',
                        'team-nifty-gmbh/flux-erp'
                    ),
                    static::getRealPackageAssetPath(
                        'resources/js/apex-charts.js',
                        'team-nifty-gmbh/flux-erp'
                    ),
                    static::getRealPackageAssetPath(
                        'resources/js/alpine.js',
                        'team-nifty-gmbh/flux-erp'
                    ),
                    static::getRealPackageAssetPath(
                        'resources/js/sw.js',
                        'team-nifty-gmbh/flux-erp'
                    ),
                    static::getRealPackageAssetPath(
                        'resources/js/tall-datatables.js',
                        'team-nifty-gmbh/tall-datatables'
                    ),
                    static::getRealPackageAssetPath(
                        'resources/js/index.js',
                        'team-nifty-gmbh/tall-calendar'
                    ),
                    static::getRealPackageAssetPath(
                        'ts/index.ts',
                        'wireui/wireui'
                    ),
                ]
            );

            if (auth()->guard('web')->check()) {
                Asset::vite(public_path('build'), [
                    static::getRealPackageAssetPath(
                        'resources/js/web-push.js',
                        'team-nifty-gmbh/flux-erp'
                    ),
                ]);
            }
        }

        /** use @extendFlux() at the end of the component, not the beginning */
        Blade::directive('extendFlux', function (string $view) {
            // Trim and remove quotes
            $view = trim($view, ' "\'');

            $path = [
                flux_path('resources/views'),
            ];

            $finder = new FileViewFinder(app('files'), $path);
            $filePath = $finder->find($view);

            return Blade::compileString(file_get_contents($filePath));
        });

        Blade::directive('canAction', function ($expression) {
            return "<?php if (resolve_static($expression, 'canPerformAction', [false])): ?>";
        });
        Blade::directive('endCanAction', function () {
            return '<?php endif; ?>';
        });

        Blade::component(App::class, 'flux::layouts.app');
        Blade::component(Printing::class, 'flux::layouts.print');
        config([
            'livewire.layout' => 'flux::layouts.app',
        ]);

        // Register Printing views as blade components
        $views[] = __DIR__ . '/../../resources/views/printing';
        $this->loadViewsFrom($views, 'print');

        View::composer('*', function () {
            Currency::default() && Number::useCurrency(Currency::default()->iso);

            try {
                if (! $this->app->runningInConsole() || $this->app->runningUnitTests()) {
                    View::share(
                        'defaultCurrency',
                        Currency::default() ?? app(Currency::class)
                    );
                } else {
                    View::share('defaultCurrency', app(Currency::class));
                }
            } catch (\Throwable) {
            }
        });
    }

    public static function getRealPackageAssetPath(string $path, string $packageName): string
    {
        $path = ltrim($path, '/');
        $relativePath = ltrim(
            substr(
                realpath(InstalledVersions::getInstallPath($packageName)),
                strlen(realpath(array_keys(ClassLoader::getRegisteredLoaders())[0] . '/../'))
            ) . '/',
            '/'
        );

        return $relativePath . $path;
    }
}
