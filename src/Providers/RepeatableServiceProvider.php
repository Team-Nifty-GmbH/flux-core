<?php

namespace FluxErp\Providers;

use FluxErp\Console\Scheduling\RepeatableManager;
use Illuminate\Support\ServiceProvider;

class RepeatableServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $manager = $this->app->make(RepeatableManager::class);

        $manager->autoDiscover(flux_path('src/Console/Commands'), 'FluxErp\Console\Commands');
        $manager->autoDiscover(flux_path('src/Jobs'), 'FluxErp\Jobs');
        $manager->autoDiscover(flux_path('src/Invokable'), 'FluxErp\Invokable');
        $manager->autoDiscover();
    }

    public function register(): void
    {
        if (! $this->app->bound(RepeatableManager::class)) {
            $this->app->singleton(RepeatableManager::class, fn (): RepeatableManager => new RepeatableManager());
        }
    }
}
