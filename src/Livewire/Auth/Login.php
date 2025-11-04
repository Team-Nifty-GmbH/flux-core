<?php

namespace FluxErp\Livewire\Auth;

use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Rule;
use Livewire\Component;

class Login extends Component
{
    use Actions;

    #[Rule(['required', 'email'])]
    public string $email;

    public ?string $password = null;

    public bool $remember = false;

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
            $this->notification()->success(__('Login link sent, check your inbox'))->send();

            return true;
        }

        if ($login) {
            $this->redirect($target);

            return true;
        } else {
            $this->reset('password');
            $this->notification()->error(__('Login failed'))->send();
            $this->js('$focus.focus(document.getElementById(\'password\'));');
        }

        return false;
    }

    public function resetPassword(): void
    {
        $this->validateOnly('email');

        $this->getPasswordBroker()->sendResetLink(['email' => $this->email]);

        $this->notification()->success(__('Password reset link sent'))->send();
    }

    public function sendMagicLink(): void
    {
        $user = Auth::guard($this->guard)
            ->getProvider()
            ->retrieveByCredentials(['email' => $this->email]);

        if ($user && method_exists($user, 'sendLoginLink')) {
            $user->sendLoginLink();
        }
    }

    protected function getPasswordBroker(): PasswordBroker
    {
        $provider = config('auth.guards.' . Auth::guard($this->guard)->name . '.provider');
        $broker = collect(config('auth.passwords'))
            ->filter(function ($item) use ($provider) {
                return $item['provider'] === $provider;
            })
            ->keys()
            ->first();

        return Password::broker($broker);
    }

    protected function tryLogin(): bool
    {
        return Auth::guard($this->guard)->attempt([
            'email' => $this->email,
            'password' => $this->password,
            'is_active' => true,
        ], $this->remember);
    }
}
