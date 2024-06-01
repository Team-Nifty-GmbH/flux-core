<?php

namespace FluxErp\Livewire\Auth;

use Illuminate\Contracts\Auth\StatefulGuard;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Logout extends Component
{
    public function render(): string
    {
        app(StatefulGuard::class)->logout();

        if (request()->hasSession()) {
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        return view('flux::livewire.auth.logout');
    }

    #[Renderless]
    public function redirectToLogin(): void
    {
        $this->redirect(route('login'), true);
    }
}
