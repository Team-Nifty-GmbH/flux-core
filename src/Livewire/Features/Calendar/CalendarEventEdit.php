<?php

namespace FluxErp\Livewire\Features\Calendar;

use FluxErp\Livewire\Forms\CalendarEventForm;
use Illuminate\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class CalendarEventEdit extends Component
{
    #[Modelable]
    public CalendarEventForm $event;

    public function render(): View
    {
        return view('flux::livewire.features.calendar.calendar-event-edit');
    }
}
