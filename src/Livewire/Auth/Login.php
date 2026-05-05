<?php

namespace FluxErp\Livewire\Auth;

use FluxErp\Models\User;
use FluxErp\Settings\SecuritySettings;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Login extends Component
{
    use Actions;

    #[Rule(['required', 'email'])]
    public string $email;

    public ?string $password = null;

    public bool $remember = false;

    public bool $showTotpChallenge = false;

    public ?string $totpCode = null;

    protected string $dashboardRoute = 'dashboard';

    protected string $guard = 'web';

    public function mount(): void
    {
        if (Auth::guard($this->guard)->check()) {
            $this->redirect(route($this->dashboardRoute));
        }

        if (! config('flux.install_done')) {
            throw HttpException::fromStatusCode(423);
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
            $guard = $this->guard();

            if (! $guard->validate(['email' => $this->email, 'password' => $this->password])) {
                return $this->failLogin();
            }

            $user = $guard->getLastAttempted();

            if (! $user instanceof User || ! $user->is_active) {
                return $this->failLogin();
            }

            if ($user->hasTwoFactorEnabled()) {
                Session::put('two_factor_login', [
                    'user_id' => $user->getKey(),
                    'remember' => $this->remember,
                    'target' => $target,
                ]);
                $this->showTotpChallenge = true;

                return true;
            }

            $guard->login($user, $this->remember);
            $this->redirect($target);

            return true;
        }

        if (! app(SecuritySettings::class)->magic_login_links_enabled) {
            $this->toast()
                ->error(__('Login by email link is disabled'))
                ->send();

            return false;
        }

        $this->sendMagicLink();
        $this->toast()
            ->success(__('Login link sent, check your inbox'))
            ->send();

        return true;
    }

    public function verifyTotpCode(): void
    {
        $loginData = Session::get('two_factor_login');

        if (
            ! is_array($loginData)
            || ! isset($loginData['user_id'], $loginData['remember'], $loginData['target'])
        ) {
            Session::forget('two_factor_login');
            $this->reset('password', 'totpCode');
            $this->showTotpChallenge = false;

            return;
        }

        $user = resolve_static(User::class, 'query')
            ->whereKey($loginData['user_id'])
            ->first();

        if (! $user || blank($this->totpCode) || ! $user->validateTwoFactorCode($this->totpCode)) {
            $this->addError('totpCode', __('Invalid code'));
            $this->reset('totpCode');

            return;
        }

        Session::forget('two_factor_login');
        $this->guard()->login($user, $loginData['remember']);
        $this->redirect($loginData['target']);
    }

    public function cancelTotpChallenge(): void
    {
        Session::forget('two_factor_login');
        $this->showTotpChallenge = false;
        $this->reset('password', 'totpCode');
    }

    public function resetPassword(): void
    {
        $this->validateOnly('email');
        $this->getPasswordBroker()->sendResetLink(['email' => $this->email]);
        $this->toast()
            ->success(__('Password reset link sent'))
            ->send();
    }

    public function sendMagicLink(): void
    {
        if (! app(SecuritySettings::class)->magic_login_links_enabled) {
            return;
        }

        $user = $this->guard()
            ->getProvider()
            ->retrieveByCredentials(['email' => $this->email]);

        if ($user && method_exists($user, 'sendLoginLink')) {
            $user->sendLoginLink();
        }
    }

    protected function guard(): StatefulGuard
    {
        return Auth::guard($this->guard);
    }

    protected function failLogin(): bool
    {
        $this->reset('password');
        $this->toast()
            ->error(__('Login failed'))
            ->send();
        $this->js('$focus.focus(document.getElementById(\'password\'));');

        return false;
    }

    protected function getPasswordBroker(): PasswordBroker
    {
        $provider = config('auth.guards.' . $this->guard()->name . '.provider');
        $broker = collect(config('auth.passwords'))
            ->filter(function ($item) use ($provider) {
                return $item['provider'] === $provider;
            })
            ->keys()
            ->first();

        return Password::broker($broker);
    }
}
