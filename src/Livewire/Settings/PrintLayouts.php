<?php

namespace FluxErp\Livewire\Settings;

use Illuminate\View\View;
use Livewire\Component;

class PrintLayouts extends Component
{

    public function render():View
    {
        return view('flux::livewire.settings.print-layout-list');
    }

}
