<?php

namespace FluxErp\Http\Livewire\Calendars;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Calendar extends Component
{
    public function render(): View|Factory|Application
    {
        return view('flux::livewire.calendars.calendar');
    }
}
