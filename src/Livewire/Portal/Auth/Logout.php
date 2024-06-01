<?php

namespace FluxErp\Livewire\Portal\Auth;

use Livewire\Attributes\Renderless;

class Logout extends \FluxErp\Livewire\Auth\Logout
{
    #[Renderless]
    public function redirectToLogin(): void
    {
        $this->redirect(route('portal.login'), true);
    }
}
