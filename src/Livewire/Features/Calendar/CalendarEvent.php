<?php

namespace FluxErp\Livewire\Features\Calendar;

use FluxErp\Livewire\Forms\CalendarEventForm;
use FluxErp\Models\Calendar;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class CalendarEvent extends Component
{
    use Actions;

    #[Modelable]
    public CalendarEventForm $event;

    public array $selectableCalendars = [];

    public function render(): View
    {
        return view('flux::livewire.features.calendar.calendar-event');
    }

    #[Renderless]
    #[On('delete-calendar-event')]
    public function delete(): bool
    {
        $eventId = $this->event->id;
        $eventId .= ! is_null($this->event->repetition) ? '|' . $this->event->repetition : '';

        if (! $this->event->was_repeatable) {
            $this->event->confirm_option = 'all';
        }

        try {
            $this->event->delete();
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->js(<<<JS
            \$modalClose('confirm-dialog');
            \$modalClose('edit-event-modal');
            calendar.getEventById('$eventId')?.remove();
        JS);

        return true;
    }

    #[Renderless]
    public function isCalendarEventRepeatable(int|string|null $calendarId): bool
    {
        return (bool) resolve_static(Calendar::class, 'query')
            ->whereKey($calendarId)
            ->value('has_repeatable_events');
    }

    #[Renderless]
    #[On('save-calendar-event')]
    public function save(): bool
    {
        if ($this->event->was_repeatable
            && $this->event->has_repeats
            && $this->event->confirm_option === 'this'
        ) {
            $this->event->confirm_option = 'future';
        }

        if (! $this->event->was_repeatable) {
            $this->event->confirm_option = 'all';
        }

        try {
            $this->event->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->js(<<<JS
            \$modalClose('confirm-dialog');
            \$modalClose('edit-event-modal');
            calendar.getEventSourceById('{$this->event->calendar_id}')?.refetch();
        JS);

        return true;
    }

    public function updatedEvent(): void
    {
        $this->skipRender();
    }
}
