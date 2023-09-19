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

    public function mount(): void
    {
        if (Auth::guard('web')->check()) {
            $this->redirect(route('dashboard'));
        }
    }

    public function render(): Factory|View|Application
    {
        return view('flux::livewire.auth.login');
    }

    public function login(): void
    {
        $this->validate();

        $login = Auth::guard('web')->attempt(['email' => $this->email, 'password' => $this->password]);

        if ($login) {
            $this->redirect(route('dashboard'));
        } else {
            $this->showPasswordReset = true;
            $this->reset('password');
            $this->notification()->error(__('Login failed'));
            $this->js('$focus.focus(document.getElementById(\'password\'));');
        }
    }

    public function resetPassword(): void
    {
        $this->validateOnly('email');

        Password::broker('users')->sendResetLink(['email' => $this->email]);

        $this->notification()->success(__('Password reset link sent'));
    }
}
