<?php

namespace FluxErp\Livewire\Auth;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Rule;
use Livewire\Component;
use WireUi\Traits\Actions;

class Login extends Component
{
    use Actions;

    #[Rule(['required', 'email'])]
    public string $email;

    #[Rule(['required'])]
    public string $password = '';

    #[Locked]
    public bool $showPasswordReset = false;

    protected string $dashboardRoute = 'dashboard';

    protected string $guard = 'web';

    public function mount(): void
    {
        if (Auth::guard($this->guard)->check()) {
            $this->redirect(route($this->dashboardRoute));
        }
    }

    public function render(): Factory|View|Application
    {
        return view('flux::livewire.auth.login');
    }

    public function login(): bool
    {
        $this->validate();

        $login = $this->tryLogin();

        if ($login) {
            $this->redirect(route($this->dashboardRoute));

            return true;
        } else {
            $this->showPasswordReset = true;
            $this->reset('password');
            $this->notification()->error(__('Login failed'));
            $this->js('$focus.focus(document.getElementById(\'password\'));');
        }

        return false;
    }

    public function resetPassword(): void
    {
        $this->validateOnly('email');

        Password::broker('users')->sendResetLink(['email' => $this->email]);

        $this->notification()->success(__('Password reset link sent'));
    }

    public function tryLogin(): bool
    {
        return Auth::guard($this->guard)->attempt(['email' => $this->email, 'password' => $this->password]);
    }
}
