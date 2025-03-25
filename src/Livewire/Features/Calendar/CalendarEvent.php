<?php

namespace FluxErp\Livewire\Features\Calendar;

use FluxErp\Livewire\Forms\CalendarEventForm;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class CalendarEvent extends Component
{
    use Actions;

    #[Modelable]
    public CalendarEventForm $event;

    public function render(): View
    {
        return view('flux::livewire.features.calendar.calendar-event');
    }

    #[Renderless]
    public function delete(): bool
    {
        $eventId = $this->event->id;

        try {
            $this->event->delete();
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->js(<<<JS
            \$modalClose('edit-event-modal');
            calendar.getEventById('$eventId')?.remove();
        JS);

        return true;
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->event->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->js(<<<JS
            \$modalClose('edit-event-modal');
            calendar.getEventSourceById('{$this->event->calendar_id}').refetch();
        JS);

        return true;
    }
}
