<?php

namespace FluxErp\Http\Livewire\Portal;

use Livewire\Component;

class Calendar extends Component
{
    public function render()
    {
        return view('flux::livewire.portal.calendar')->layout('flux::components.layouts.portal');
    }
}
