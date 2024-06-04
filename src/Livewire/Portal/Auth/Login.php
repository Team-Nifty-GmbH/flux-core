<?php

namespace FluxErp\Livewire\Portal\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class Login extends \FluxErp\Livewire\Auth\Login
{
    protected string $dashboardRoute = 'portal.dashboard';

    protected string $guard = 'address';

    public function render(): Application|Factory|View
    {
        return view('flux::livewire.portal.auth.login');
    }

    protected function tryLogin(): bool
    {
        return Auth::guard('address')->attempt([
            'login_name' => $this->email,
            'password' => $this->password,
            'is_active' => true,
            'can_login' => true,
        ]);
    }

    protected function retrieveUserByCredentials(): ?Authenticatable
    {
        return Auth::guard('address')
            ->getProvider()
            ->retrieveByCredentials(['login_name' => $this->email]);
    }
}
