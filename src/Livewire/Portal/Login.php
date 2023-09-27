<?php

namespace FluxErp\Livewire\Portal;

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

    public function tryLogin(): bool
    {
        return Auth::guard('address')->attempt(['login_name' => $this->email, 'password' => $this->password]);
    }
}
