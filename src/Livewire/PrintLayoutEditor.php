<?php

namespace FluxErp\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PrintLayoutEditor extends Component
{

    #[Layout('flux::layouts.print-layout-editor')]
    public function render(): View
    {
        return view('flux::livewire.a4-page');
    }

}
