<?php

namespace FluxErp\Providers;

use FluxErp\View\Layouts\App;
use FluxErp\View\Layouts\Printing;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\FileViewFinder;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        /** use @extendFlux() at the end of the component, not the beginning */
        Blade::directive('extendFlux', function (string $expression) {
            $finder = new FileViewFinder(app('files'), [flux_path('resources/views')]);
            $filePath = $finder->find($expression);

            return Blade::compileString(file_get_contents($filePath));
        });

        Blade::component(App::class, 'layouts.app');
        Blade::component(Printing::class, 'layouts.print.index');
        Blade::component(Printing::class, 'layouts.print');
        config([
            'livewire.layout' => 'flux::layouts.app',
        ]);

        // Register Printing views as blade components
        $views[] = __DIR__ . '/../../resources/views/printing';
        $this->loadViewsFrom($views, 'print');
    }
}
