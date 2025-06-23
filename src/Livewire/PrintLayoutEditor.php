<?php

namespace FluxErp\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PrintLayoutEditor extends Component
{

    #[Layout('flux::layouts.printing',
        [   'edit' => true,
            'signaturePath' => false
        ])
    ]
    public function render(): View
    {
        return view('flux::livewire.print-layout-editor');
    }

}
