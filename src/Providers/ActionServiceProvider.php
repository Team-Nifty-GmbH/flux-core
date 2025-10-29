<?php

namespace FluxErp\Providers;

use FluxErp\Actions\ActionManager;
use Illuminate\Support\ServiceProvider;

class ActionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! $this->app->bound(ActionManager::class)) {
            $this->app->singleton(ActionManager::class, fn (): ActionManager => new ActionManager());
        }
    }

    public function boot(): void
    {
        $manager = $this->app->make(ActionManager::class);

        $manager->autoDiscover(flux_path('src/Actions'), 'FluxErp\Actions');
        $manager->autoDiscover();
    }
}
