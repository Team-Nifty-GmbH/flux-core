<?php

namespace FluxErp\Livewire\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Rule;
use Livewire\Component;
use WireUi\Traits\Actions;

class Login extends Component
{
    use Actions;

    #[Rule(['required', 'email'])]
    public string $email;

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

        if (! config('flux.install_done')) {
            $this->redirect(route('flux.install'));
        }
    }

    public function render(): Factory|View|Application
    {
        return view('flux::livewire.auth.login');
    }

    public function login(): bool
    {
        $target = Session::get('url.intended', route($this->dashboardRoute));
        $this->validate();

        if ($this->password) {
            $login = $this->tryLogin();
        } else {
            $this->sendMagicLink();
            $this->notification()->success(__('Login link sent, check your inbox'));

            return true;
        }

        if ($login) {
            $this->redirect($target);

            return true;
        } else {
            $this->showPasswordReset = true;
            $this->reset('password');
            $this->notification()->error(__('Login failed'));
            $this->js('$focus.focus(document.getElementById(\'password\'));');
        }

        return false;
    }

    public function sendMagicLink(): void
    {
        $user = $this->retrieveUserByCredentials();
        if ($user && method_exists($user, 'sendLoginLink')) {
            $user->sendLoginLink();
        }
    }

    public function resetPassword(): void
    {
        $this->validateOnly('email');

        Password::broker('users')->sendResetLink(['email' => $this->email]);

        $this->notification()->success(__('Password reset link sent'));
    }

    protected function tryLogin(): bool
    {
        return Auth::guard($this->guard)->attempt([
            'email' => $this->email,
            'password' => $this->password,
            'is_active' => true,
        ]);
    }

    protected function retrieveUserByCredentials(): ?Authenticatable
    {
        return Auth::guard('web')
            ->getProvider()
            ->retrieveByCredentials(['email' => $this->email]);
    }
}
