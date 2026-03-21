<?php

namespace FluxErp\Livewire\Auth;

use FluxErp\Models\User;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            $user = $this->resolveUser();

            if (! $user || ! Hash::check($this->password, $user->password) || ! $user->is_active) {
                $this->reset('password');
                $this->toast()
                    ->error(__('Login failed'))
                    ->send();
                $this->js('$focus.focus(document.getElementById(\'password\'));');

                return false;
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

            Auth::guard($this->guard)->login($user, $this->remember);
            $this->redirect($target);

            return true;
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

        if (! $loginData) {
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
        Auth::guard($this->guard)->login($user, $loginData['remember']);
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

    protected function resolveUser(): ?User
    {
        return resolve_static(User::class, 'query')
            ->where('email', $this->email)
            ->first();
    }
}
