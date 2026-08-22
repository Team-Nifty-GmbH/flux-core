<?php

namespace FluxErp\Providers;

use FluxErp\Support\Auth\PermissionSet;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define(resolve_static(PermissionSet::class, 'blanketProbeAbility'), fn (): false => false);

        Gate::before(function (Authenticatable $user, $ability): ?true {
            return method_exists($user, 'hasRole') && $user->hasRole('Super Admin')
                ? true
                : null;
        });
    }
}
