<?php

namespace FluxErp\Http\Livewire\Dashboard;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use WireUi\Traits\Actions;

class Dashboard extends Component
{
    use Actions;

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.dashboard.dashboard');
    }
}
