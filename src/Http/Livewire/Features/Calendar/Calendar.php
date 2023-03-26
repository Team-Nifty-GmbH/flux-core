<?php

namespace FluxErp\Http\Livewire\Features\Calendar;

use FluxErp\Http\Requests\CreateCalendarEventRequest;
use FluxErp\Http\Requests\UpdateCalendarEventRequest;
use FluxErp\Models\Address;
use FluxErp\Models\Calendar as CalendarModel;
use FluxErp\Models\CalendarEvent;
use FluxErp\Models\User;
use FluxErp\Services\CalendarEventService;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\View\View;
use Livewire\Component;

class Calendar extends Component
{
    public array $calendars = [];

    public array $calendar = [];

    public array $personalCalendars = [];

    public array $activeCalendars;

    public array $calendarTree = [];

    public ?int $calendarId;

    public array $invites = [];

    public string $tab = 'users';

    public string $search = '';

    public array $searchResults = [];

    public array $events = [];

    public array $calendarEvent = [
        'calendar_id' => null,
        'starts_at' => null,
        'ends_at' => null,
        'is_all_day' => false,
        'status' => null,
    ];

    public bool $eventModal = false;

    public ?Carbon $gridStartsAt = null;

    public ?Carbon $gridEndsAt = null;

    // Settings
    public bool $editable = true;

    public Carbon $initialDate;

    // Can be set to:
    // dayGridMonth, dayGridWeek, dayGridDay,
    // timeGridWeek, timeGridDay, timeGrid,
    // listYear, listMonth, listWeek, listDay, list
    public string $initialView = 'dayGridMonth';

    public string $editEventComponent = 'calendar.new-event';

    protected function getRules(): array
    {
        $rules = Arr::prependKeysWith(
            ($this->calendarEvent['id'] ?? false)
                ? (new UpdateCalendarEventRequest())->rules()
                : (new CreateCalendarEventRequest())->rules(),
            'calendarEvent.');

        $rules['calendarEvent.model_type'][0] = 'required_without:calendarEvent.calendar_id';
        $rules['calendarEvent.model_id'][0] = 'required_without:calendarEvent.calendar_id';

        return $rules;
    }

    public function attendEvent(CalendarEvent $event): void
    {
        if (auth()->user() instanceof Address) {
            $event->invitedAddresses()->syncWithoutDetaching([auth()->id() => ['status' => 'accepted']]);
        } else {
            $event->invitedUsers()->syncWithoutDetaching([auth()->id() => ['status' => 'accepted']]);
        }

        $this->eventModal = false;

        $this->skipRender();
    }

    public function updatedSearch(): void
    {
        $model = $this->tab === 'users' ? User::class : Address::class;
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

        $event = CalendarEvent::query()
            ->whereKey($this->calendarEvent['id'] ?? null)
            ->firstOrFail();

        $event->delete();

        $events = Arr::keyBy($this->events, 'id');
        unset($events[$this->calendarEvent['id']]);
        $this->events = array_values($events);

        $this->eventModal = false;

        $this->emit('refreshCalendar');
        $this->skipRender();
    }

    public function getEvents(string $from, string $to): void
    {
        $this->gridStartsAt = Carbon::parse($from)->subMonth();
        $this->gridEndsAt = Carbon::parse($to)->addMonth();
        $this->events();

        $this->skipRender();
    }

    public function events(): void
    {
        $this->events = $this->mapEvents(CalendarEvent::query()
            ->whereIntegerInRaw('calendar_id', $this->activeCalendars)
            ->where(function (Builder $query) {
                return $query
                    ->whereBetween('starts_at', [$this->gridStartsAt, $this->gridEndsAt])
                    ->orWhereBetween('ends_at', [$this->gridStartsAt, $this->gridEndsAt]);
            })
            ->get())
            ->toArray();

        $this->events = Arr::keyBy($this->events, 'id');

        $this->invitedEvents();
    }

    public function mapEvents(Model|Collection $collection): Collection
    {
        $collection = $collection instanceof Collection ? $collection : collect([$collection]);

        return $collection->map(function ($event) {
            return [
                'allDay' => $event->is_all_day,
                'start' => $event->starts_at,
                'end' => $event->ends_at,
                'title' => $event->title,
                'id' => $event->id,
                'color' => $event->calendar->color,
            ];
        });
    }

    public function invitedEvents(): void
    {
        $invited = $this->mapEvents(auth()->user()
            ->calendarEventInvites()
            ->whereIntegerInRaw('model_calendar_id', $this->activeCalendars)
            ->whereNot('status', 'declined')
            ->where(function (Builder $query) {
                return $query
                    ->whereBetween('calendar_events.starts_at', [$this->gridStartsAt, $this->gridEndsAt])
                    ->orWhereBetween('calendar_events.ends_at', [$this->gridStartsAt, $this->gridEndsAt]);
            })
            ->get())
            ->toArray();

        $invited = Arr::keyBy($invited, 'id');

        $this->events = array_merge($this->events, $invited);
    }

    public function getListeners(): array
    {
        $channel = (new CalendarModel())->broadcastChannel();

        $listeners['echo-private:' . $channel . ',.CalendarCreated'] = 'refreshCalendars';

        foreach ($this->activeCalendars as $calendar) {
            $calendarChannel = 'echo-private:' . $channel . '.' . $calendar;
            $listeners[$calendarChannel . ',.CalendarDeleted'] = 'refreshCalendars';
            $listeners[$calendarChannel . ',.CalendarUpdated'] = 'refreshCalendars';
            $listeners[$calendarChannel . ',.CalendarEventUpdated'] = 'refreshCalendarEvents';
            $listeners[$calendarChannel . ',.CalendarEventDeleted'] = 'refreshCalendarEvents';
            $listeners[$calendarChannel . ',.CalendarEventCreated'] = 'refreshCalendarEvents';
        }

        $listeners['echo-private:' . auth()->user()->broadcastChannel() . ',.CalendarEventInviteCreated'] = 'addInvite';

        $listeners['updatedCalendar'] = 'updatedCalendar';

        return $listeners;
    }

    public function mount(bool $showPersonalCalendar = true): void
    {
        $this->getCalendars();
        $this->activeCalendars = $this->getCalendarIds($this->calendars);

        $this->editable = user_can('api.calendar-events.put');

        $this->getInvites();

        if ($showPersonalCalendar) {
            $this->getPersonalCalendars();
            $this->activeCalendars = array_merge(
                $this->getCalendarIds($this->personalCalendars),
                $this->activeCalendars
            );
        }

        $this->calendarEvent['calendar_id'] = $this->activeCalendars[0] ?? null;
    }

    public function updatedCalendar(): void
    {
        $this->getCalendars();
        $this->getPersonalCalendars();
    }

    public function editPersonalCalendar(?int $id = null): void
    {
        $this->emitTo('calendar-edit', 'show', $id, $this->personalCalendars);
        $this->skipRender();
    }

    /**
     * @param string|array|null $status
     */
    public function updateInvites(string|array|null $status = null): void
    {
        $this->getInvites($status);
        $this->skipRender();
    }

    /**
     * @param string|array|null $status
     */
    public function getInvites(string|array|null $status = null): void
    {
        $query = auth()->user()
            ->calendarEventInvites();

        if (is_null($status)) {
            $query->whereNull('status');
        } else {
            $status = (array) $status;
            $query->whereIn('status', $status);
        }

        $this->invites = $query->orderBy('starts_at')->get()->toArray();
    }

    public function addInvite($event): void
    {
        $this->invites[] = auth()->user()
            ->calendarEventInvites()
            ->wherePivot('id', $event['model']['id'])
            ->first()
            ->toArray();
    }

    public function getCalendars(): void
    {
        $this->calendars = [];

        $calendars = CalendarModel::query()
            ->whereNull('parent_id')
            ->where('is_public', true)
            ->with('children.parent')
            ->get()
            ->toArray();

        $this->toFlatTree($calendars, 'calendars');
    }

    public function toFlatTree(array $array, string $name, string $slug = null): void
    {
        foreach ($array as $item) {
            $sanitized = Arr::only($item, ['id', 'name', 'event_component', 'color']);
            $sanitized['slug'] = $slug;
            $this->{$name}[] = $sanitized;

            if ($item['children'] ?? false) {
                $this->toFlatTree($item['children'], $name, $slug . '-' . $item['id']);
            }
        }
    }

    private function getCalendarIds(array $calendars): array
    {
        return collect($calendars)->pluck('id')->toArray();
    }

    public function getPersonalCalendars(): void
    {
        $this->personalCalendars = [];

        $calendars = CalendarModel::query()
            ->where('user_id', auth()->id())
            ->with('children')
            ->get()
            ->toArray();
        $this->toFlatTree($calendars, 'personalCalendars');

        $this->personalCalendars = collect($this->personalCalendars)->unique('id')->toArray();
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
        $calendarEventQuery = CalendarEvent::query()
            ->whereKey($event);
        $calendarEvent = auth()->user() instanceof User
            ? $calendarEventQuery->with([
                'invitedUsers',
                'invitedAddresses',
            ])
                ->first()
        : $calendarEventQuery->first();

        $invite = auth()->user()->calendarEventInvites()->where('calendar_event_id', $calendarEvent->id)->first();

        $this->calendarEvent = Arr::only(
            $calendarEvent->toArray(),
            [
                'id',
                'title',
                'description',
                'starts_at',
                'ends_at',
                'is_all_day',
                'model_type',
                'model_id',
                'invited_users',
                'invited_addresses',
                'calendar_id',
                'created_by',
            ]
        );

        $this->calendarEvent['status'] = $invite?->pivot->status;
        $this->calendarEvent['calendar_id'] = $invite
            ? $invite->pivot->model_calendar_id
            : $this->calendarEvent['calendar_id'];

        $this->calendarEvent['disabled'] = ! $calendarEvent->createdBy?->is(auth()->user());

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
        if (! user_can('api.calendar-events.post')) {
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
        $this->emitTo('folder-tree', 'modelSaved', $calendarEvent->id);
        $this->emit('refreshCalendar');

        $this->skipRender();
    }

    public function refreshCalendarEvents(): void
    {
        $this->events();
        $this->emit('refreshCalendar');
    }

    public function refreshCalendars(): void
    {
        $this->getCalendars();

        $this->emit('refreshCalendar');
    }

    /**
     * @throws Exception
     */
    public function render(): Factory|View
    {
        return view('flux::livewire.features.calendar.calendar');
    }

    public function updatedActiveCalendars(): void
    {
        $this->events();
        $this->emit('refreshCalendar');

        $this->skipRender();
    }

    public function updatedCalendarEvent(): void
    {
        $this->skipRender();
    }

    public function updatedCalendarEventStatus($value): void
    {
        $calendarEvent = CalendarEvent::query()->whereKey($this->calendarEvent['id'])->firstOrFail();

        $this->inviteStatus($calendarEvent, $value);
    }

    public function inviteStatus(CalendarEvent $event, string $status): void
    {
        $calendarId = $this->calendarEvent['calendar_id'];

        $invites = Arr::keyBy($this->invites, 'id');
        unset($invites[$event->id]);

        auth()->user()
            ->calendarEventInvites()
            ->updateExistingPivot(
                $event->id,
                [
                    'status' => $status,
                    'model_calendar_id' => $calendarId,
                ]
            );

        $this->invites = array_values($invites);

        if ($status === 'declined') {
            $this->events = array_values(collect($this->events)->whereNotIn('id', [$event->id])->toArray());
        } else {
            $this->events = array_merge($this->events, $this->mapEvents(collect([$event]))->toArray());
        }

        $this->emit('refreshCalendar');
        $this->skipRender();
    }

    public function addInvitedRecord(int $id): void
    {
        $model = $this->tab === 'users' ? User::class : Address::class;

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
