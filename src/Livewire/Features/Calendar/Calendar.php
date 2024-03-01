<?php

namespace FluxErp\Livewire\Features\Calendar;

use Exception;
use FluxErp\Actions\CalendarEvent\CreateCalendarEvent;
use FluxErp\Actions\CalendarEvent\UpdateCalendarEvent;
use FluxErp\Models\Address;
use FluxErp\Models\CalendarEvent;
use FluxErp\Models\User;
use FluxErp\Services\CalendarEventService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\View\View;
use Livewire\Component;

class Calendar extends Component
{
    public array $calendar = [];

    public ?int $calendarId;

    public string $tab = 'users';

    public string $search = '';

    public array $searchResults = [];

    public array $calendarEvent = [
        'calendar_id' => null,
        'starts_at' => null,
        'ends_at' => null,
        'is_all_day' => false,
        'status' => null,
    ];

    public array $events = [];

    public bool $eventModal = false;

    // Settings
    public bool $editable = true;

    public string $editEventComponent = 'calendar.new-event';

    public function getRules(): array
    {
        $rules = Arr::prependKeysWith(
            ($this->calendarEvent['id'] ?? false)
                ? UpdateCalendarEvent::make([])->getRules()
                : CreateCalendarEvent::make([])->getRules(),
            'calendarEvent.');

        $rules['calendarEvent.model_type'][0] = 'required_without:calendarEvent.calendar_id';
        $rules['calendarEvent.model_id'][0] = 'required_without:calendarEvent.calendar_id';

        return $rules;
    }

    public function updatedSearch(): void
    {
        $model = app($this->tab === 'users' ? User::class : Address::class);
        $this->searchResults = $this->search ? $model::search($this->search)->get()->toArray() : [];

        $this->skipRender();
    }

    public function updatedTab(): void
    {
        $this->search = '';
        $this->searchResults = [];

        $this->skipRender();
    }

    public function delete(): void
    {
        if (! user_can('api.calendar-events.{id}.delete')) {
            return;
        }

        $event = app(CalendarEvent::class)->query()
            ->whereKey($this->calendarEvent['id'] ?? null)
            ->firstOrFail();

        $event->delete();

        $events = Arr::keyBy($this->events, 'id');
        unset($events[$this->calendarEvent['id']]);
        $this->events = array_values($events);

        $this->eventModal = false;

        $this->dispatch('refreshCalendar');
        $this->skipRender();
    }

    public function mount(bool $showPersonalCalendar = true): void
    {
        $this->editable = resolve_static(UpdateCalendarEvent::class, 'canPerformAction', [false]);
    }

    public function onDayClick(?string $dateString = null): void
    {
        // This method gets called when a day is clicked
        $this->resetErrorBag();

        $now = Carbon::now();
        $date = $dateString ?
            Carbon::parse($dateString)->setTime($now->hour, $now->minute)->floorMinutes(15) :
            $now;

        $calendarId = $this->calendarEvent['calendar_id'];
        $this->reset('calendarEvent');

        $this->calendarEvent['starts_at'] = $date->format('Y-m-d H:i');
        $this->calendarEvent['ends_at'] = $date->format('Y-m-d H:i');
        $this->calendarEvent['calendar_id'] = $calendarId;
        $this->calendarEvent['disabled'] = false;

        if (
            Arr::keyBy(array_merge($this->calendars, $this->personalCalendars), 'id')[$calendarId]['user_id']
            ?? false
        ) {
            $this->calendarEvent['model_type'] = auth()->user()->getMorphClass();
            $this->calendarEvent['model_id'] = auth()->id();
        }

        $this->eventModal = true;
        $this->skipRender();
    }

    public function onEventClick(int $event): void
    {
        $this->resetErrorBag();

        // This method is called when an event is clicked
        $this->calendarEvent = app(CalendarEvent::class)->query()
            ->whereKey($event)
            ->first()
            ->toArray();

        $this->calendarEvent['disabled'] = false;

        $this->eventModal = true;
    }

    public function onEventDropped(CalendarEvent $event, $data): void
    {
        // This method is called when an event was dropped on a new day
        $event->starts_at = Date::parse($data['start'])->format('Y-m-d H:i');
        $event->ends_at = Date::parse($data['end'] ?? $data['start'])->format('Y-m-d H:i');
        $this->calendarEvent = $event->toArray();
        $this->save();

        $this->skipRender();
    }

    public function save(): void
    {
        if (! resolve_static(CreateCalendarEvent::class, 'canPerformAction', [false])) {
            return;
        }

        $this->calendarEvent['starts_at'] = Date::parse($this->calendarEvent['starts_at'])
            ->format('Y-m-d H:i');
        $this->calendarEvent['ends_at'] = Date::parse($this->calendarEvent['ends_at'])
            ->format('Y-m-d H:i');

        $this->calendarId = $this->calendarEvent['calendar_event']['calendar_id'] ?? null;
        $validated = $this->validate();

        $function = ($this->calendarEvent['id'] ?? false) ? 'update' : 'create';

        $response = (new CalendarEventService())->{$function}($validated['calendarEvent']);

        $calendarEvent = $response['data'] ?: $response;

        if ($function === 'update') {
            $events = Arr::keyBy($this->events, 'id');
            $events[$calendarEvent->id] = $this->mapEvents($calendarEvent)->toArray()[0];
        } else {
            $events = array_merge($this->events, $this->mapEvents($calendarEvent)->toArray());
        }

        $this->events = array_values($events);

        $this->eventModal = false;
        $this->dispatch('modelSaved', $calendarEvent->id)->to('folder-tree');
        $this->dispatch('refreshCalendar');

        $this->skipRender();
    }

    /**
     * @throws Exception
     */
    public function render(): Factory|View
    {
        return view('flux::livewire.features.calendar.calendar');
    }

    public function updatedCalendarEventStatus($value): void
    {
        $calendarEvent = app(CalendarEvent::class)->query()
            ->whereKey($this->calendarEvent['id'])
            ->firstOrFail();

        $this->inviteStatus($calendarEvent, $value);
    }

    public function addInvitedRecord(int $id): void
    {
        $model = app($this->tab === 'users' ? User::class : Address::class);

        $this->addInvitee($model::query()->whereKey($id)->first());
        $this->skipRender();
    }

    private function addInvitee(?Model $model): void
    {
        if (is_null($model)) {
            return;
        }

        $array = Arr::only($model->toArray(), ['id', 'name']);

        if ($model instanceof Address) {
            $this->calendarEvent['invited_addresses'][] = $array;
        } else {
            $this->calendarEvent['invited_users'][] = $array;
        }

        $this->skipRender();
    }
}
