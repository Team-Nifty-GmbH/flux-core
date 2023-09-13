<?php

namespace FluxErp\Livewire\Widgets;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Generic extends Component
{
    public function render(): View|Factory|Application
    {
        return view('flux::livewire.widgets.generic');
    }
}
