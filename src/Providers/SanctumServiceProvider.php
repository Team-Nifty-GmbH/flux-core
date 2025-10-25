<?php

namespace FluxErp\Providers;

use FluxErp\Support\Sanctum\Guard;
use Illuminate\Auth\RequestGuard;

class SanctumServiceProvider extends \Laravel\Sanctum\SanctumServiceProvider
{
    protected function createGuard($auth, $config): RequestGuard
    {
        return new RequestGuard(
            new Guard($auth, config('sanctum.expiration'), data_get($config, 'provider')),
            request(),
            $auth->createUserProvider(data_get($config, 'provider') ?? null)
        );
    }
}
