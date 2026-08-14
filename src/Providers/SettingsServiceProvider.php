<?php

namespace FluxErp\Providers;

use FluxErp\Settings\SettingsManager;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelSettings\SettingsContainer;

class SettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $manager = $this->app->make(SettingsManager::class);

        $manager->autoDiscover(flux_path('src/Settings'), 'FluxErp\Settings');
        $manager->autoDiscover();

        // Spatie binds its settings classes while registering, so everything discovered
        // afterwards, by this package or by any other, needs a second pass.
        $this->app->booted(function (): void {
            $this->app->make(SettingsContainer::class)->clearCache()->registerBindings();
        });
    }

    public function register(): void
    {
        $this->app->singleton(SettingsManager::class);
    }
}
