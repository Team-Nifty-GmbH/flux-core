<?php

namespace FluxErp\Providers;

use FluxErp\Console\Scheduling\RepeatableManager;
use Illuminate\Support\ServiceProvider;

class RepeatableServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RepeatableManager::class, function (): RepeatableManager {
            $manager = new RepeatableManager();
            $manager->autoDiscover(flux_path('src/Console/Commands'), 'FluxErp\Console\Commands');
            $manager->autoDiscover(flux_path('src/Jobs'), 'FluxErp\Jobs');
            $manager->autoDiscover(flux_path('src/Invokable'), 'FluxErp\Invokable');
            $manager->autoDiscover();

            return $manager;
        });
    }
}
