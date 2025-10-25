<?php

namespace FluxErp\Providers;

use FluxErp\Actions\ActionManager;
use Illuminate\Support\ServiceProvider;

class ActionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ActionManager::class, function (): ActionManager {
            $manager = new ActionManager();

            $manager->autoDiscover(flux_path('src/Actions'), 'FluxErp\Actions');
            $manager->autoDiscover();

            return $manager;
        });
    }
}
