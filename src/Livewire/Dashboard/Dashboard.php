<?php

namespace FluxErp\Livewire\Dashboard;

use FluxErp\Livewire\Support\Dashboard as BaseDashboard;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class Dashboard extends BaseDashboard
{
    public function render(): View|Factory|Application
    {
        return view('flux::livewire.dashboard.dashboard');
    }
}
