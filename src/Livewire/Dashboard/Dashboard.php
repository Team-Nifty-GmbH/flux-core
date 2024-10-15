<?php

namespace FluxErp\Livewire\Dashboard;

use FluxErp\Traits\RendersWidgets;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use WireUi\Traits\Actions;

class Dashboard extends Component
{
    use Actions, RendersWidgets;

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.dashboard.dashboard');
    }
}
