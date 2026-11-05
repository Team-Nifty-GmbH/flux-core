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

    public function editComponent(): string
    {
        $editComponent = data_get($this->event, 'edit_component');

        return is_string($editComponent)
            && livewire_component_exists($editComponent)
            && is_a(
                app('livewire.factory')->resolveComponentClass($editComponent),
                CalendarEvent::class,
                true
            )
                ? $editComponent
                : 'features.calendar.calendar-event';
    }

    public function updatedEvent(): void
    {
        if ($this->currentEditComponent !== data_get($this->event, 'edit_component')) {
            return;
        }

        $this->skipRender();
    }
}
