<?php

namespace FluxErp\Providers;

use FluxErp\Support\Mentions\MentionableTypeManager;
use Illuminate\Support\ServiceProvider;

class MentionableTypeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->make(MentionableTypeManager::class)->autoDiscover();
    }

    public function register(): void
    {
        $this->app->singleton(MentionableTypeManager::class);
    }
}
