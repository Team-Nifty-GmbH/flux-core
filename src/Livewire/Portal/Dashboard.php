<?php

namespace FluxErp\Livewire\Portal;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(): View
    {
        return view('flux::livewire.portal.dashboard');
    }
}
