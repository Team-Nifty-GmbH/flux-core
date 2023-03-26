<?php

namespace FluxErp\Http\Livewire\Auth;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Login extends Component
{
    public function render(): Factory|View|Application
    {
        return view('flux::livewire.auth.login');
    }
}
