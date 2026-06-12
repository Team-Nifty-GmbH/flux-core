<?php

namespace FluxErp\Providers;

use FluxErp\ShareTargetActions\ShareTargetActionManager;
use FluxErp\ShareTargetActions\UploadPurchaseInvoice;
use Illuminate\Support\ServiceProvider;

class ShareTargetActionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->make(ShareTargetActionManager::class)
            ->register(UploadPurchaseInvoice::class);
    }

    public function register(): void
    {
        $this->app->singleton(ShareTargetActionManager::class);
    }
}
