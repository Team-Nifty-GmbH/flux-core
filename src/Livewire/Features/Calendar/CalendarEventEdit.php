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

    protected ?string $currentEditComponent = null;

    public function render(): View
    {
        return view('flux::livewire.features.calendar.calendar-event-edit');
    }

    public function boot(): void
    {
        $this->currentEditComponent = data_get($this->event, 'edit_component');
    }

    public function updatedEvent(): void
    {
        if ($this->currentEditComponent === data_get($this->event, 'edit_component')) {
            return;
        }

        $this->skipRender();
    }
}
