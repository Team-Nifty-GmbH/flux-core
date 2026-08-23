<?php

namespace FluxErp\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function (Authenticatable $user, $ability): ?true {
            return method_exists($user, 'hasRole') && $user->hasRole('Super Admin')
                ? true
                : null;
        });
    }
}
