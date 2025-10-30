<?php

namespace FluxErp\Providers;

use FluxErp\Widgets\WidgetManager;
use Illuminate\Support\ServiceProvider;

class WidgetServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $manager = $this->app->make(WidgetManager::class);

        $manager->autoDiscoverWidgets(flux_path('src/Livewire/Widgets'), 'FluxErp\Livewire\Widgets');
        $manager->autoDiscoverWidgets();
    }

    public function register(): void
    {
        $this->app->singleton(WidgetManager::class);
    }
}
