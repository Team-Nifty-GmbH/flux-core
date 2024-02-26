<?php

namespace FluxErp\Livewire\Portal;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Orders extends Component
{
    public function render(): View|Factory|Application
    {
        return view('flux::livewire.portal.orders');
    }
}
