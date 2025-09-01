<?php

namespace FluxErp\Providers;

use Composer\Autoload\ClassLoader;
use Composer\InstalledVersions;
use FluxErp\Facades\Asset;
use FluxErp\View\Layouts\App;
use FluxErp\View\Layouts\Printing;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use TallStackUi\Facades\TallStackUi;

class ViewServiceProvider extends ServiceProvider
{
    public static function getRealPackageAssetPath(string $path, string $packageName): string
    {
        $path = ltrim($path, '/');
        $relativePath = ltrim(
            substr(
                str_replace(DIRECTORY_SEPARATOR, '/', realpath(InstalledVersions::getInstallPath($packageName))),
                strlen(realpath(array_keys(ClassLoader::getRegisteredLoaders())[0] . '/../'))
            ) . '/',
            '/'
        );

        return $relativePath . $path;
    }

    public function boot(): void
    {
        Vite::useAggressivePrefetching();

        if (
            (! $this->app->runningInConsole() || $this->app->runningUnitTests())
            && file_exists(public_path('build/manifest.json'))
        ) {
            // get the real path for the flux package root folder
            $this->bootAssets();
        }

        $this->customizeTallstackUi();

        $this->registerViews();

        $this->bootBladeDirectives();
    }

    protected function bootAssets(): void
    {
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

    protected function bootBladeDirectives(): void
    {
        Blade::if('canAction', function (string $action) {
            return resolve_static($action, 'canPerformAction', [false]);
        });
    }

    protected function customizeTallstackUi(): void
    {
        TallStackUi::personalize()
            ->avatar()
            ->block('wrapper.class', 'inline-flex shrink-0 items-center justify-center overflow-hidden !bg-secondary-200');

        TallStackUi::personalize()
            ->dropdown()
            ->block('wrapper.second', 'relative inline-block text-left w-full');
        TallStackUi::personalize()
            ->dropdown('items')
            ->block('item')
            ->replace('whitespace-nowrap', 'whitespace-normal');
        TallStackUi::personalize()
            ->scope('calendar')
            ->dropdown()
            ->block('wrapper.second', 'relative inline-block text-left w-full')
            ->block('floating.class', 'w-full');

        TallStackUi::personalize()
            ->badge()
            ->block('wrapper.class', 'outline-hidden inline-flex items-center border px-2 py-0.5');
        TallStackUi::personalize()
            ->button()
            ->block('wrapper.sizes.md', 'text-sm px-4 py-2');
        TallStackUi::personalize()
            ->form('label')
            ->block('text', 'block text-sm font-medium text-gray-700 dark:text-gray-400');

        TallStackUi::personalize()
            ->card('min-h-full')
            ->block('wrapper.first', 'flex justify-center gap-4 w-full min-h-full');

        TallStackUi::personalize()
            ->card()
            ->block('header.wrapper.border', 'dark:border-b-dark-600 border-b border-gray-100 p-2')
            ->block('header.text.size', 'text-sm font-medium w-full');

        TallStackUi::personalize()
            ->slide('notifications')
            ->block('body', 'soft-scrollbar dark:text-dark-300 grow overflow-y-auto rounded-b-xl text-gray-700');

        TallStackUi::personalize()
            ->toast()
            ->block('buttons.wrapper.second', 'flex min-h-full flex-col justify-between')
            ->block('buttons.close.wrapper', 'ml-2 flex shrink-0');

        TallStackUi::personalize()
            ->card('w-auto')
            ->block('wrapper.first', 'flex justify-center gap-4 w-auto');
        TallStackUi::personalize()
            ->card()
            ->block('wrapper.first', 'flex justify-center gap-4 w-full');

        TallStackUi::personalize()
            ->toast('relative')
            ->block('wrapper.first', 'pointer-events-none inset-0 flex flex-col items-end justify-end gap-y-2 px-4 py-4')
            ->block('wrapper.third', 'dark:bg-dark-700 pointer-events-auto w-full w-full overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5')
            ->block('buttons.wrapper.second', 'flex min-h-full flex-col justify-between')
            ->block('buttons.close.wrapper', 'ml-2 flex shrink-0');

        TallStackUi::personalize()
            ->layout('header')
            ->block('wrapper', 'dark:bg-dark-700 dark:border-dark-600 sticky top-0 z-10 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8');

        TallStackUi::personalize()
            ->modal('fullscreen')
            ->block('wrapper.fourth', 'dark:bg-dark-700 relative flex w-full transform flex-col rounded-xl bg-white text-left shadow-xl transition-all min-h-screen')
            ->block('body', 'dark:text-dark-300 grow rounded-b-xl py-5 text-gray-700 px-4 min-h-screen');

        TallStackUi::personalize()
            ->modal('headless')
            ->block('body', 'dark:text-dark-300 grow text-gray-700');
    }

    protected function registerViews(): void
    {
        Blade::component(App::class, 'flux::layouts.app');
        Blade::component(Printing::class, 'flux::layouts.print');
        config([
            'livewire.layout' => 'flux::layouts.app',
        ]);

        // Register Printing views as blade components
        $views[] = __DIR__ . '/../../resources/views/printing';
        $this->loadViewsFrom($views, 'print');
    }
}
