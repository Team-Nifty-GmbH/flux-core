<?php

namespace FluxErp\Providers;

use FluxErp\VendorClasses\Guard;
use Illuminate\Auth\RequestGuard;

class SanctumServiceProvider extends \Laravel\Sanctum\SanctumServiceProvider
{
    public function register()
    {
        parent::register();

    }

    /**
     * Register the guard.
     *
     * @param \Illuminate\Contracts\Auth\Factory  $auth
     * @param array $config
     */
    protected function createGuard($auth, $config): RequestGuard
    {
        return new RequestGuard(
            new Guard($auth, config('sanctum.expiration'), $config['provider']),
            request(),
            $auth->createUserProvider($config['provider'] ?? null)
        );
    }
}
