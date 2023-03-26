<?php

namespace FluxErp\Providers;

use FluxErp\View\Layouts\App;
use FluxErp\View\Layouts\Printing;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

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
