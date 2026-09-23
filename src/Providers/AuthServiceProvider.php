<?php

namespace FluxErp\Providers;

use FluxErp\Models\MailAccount;
use FluxErp\Policies\MailAccountPolicy;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        MailAccount::class => MailAccountPolicy::class,
    ];

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
