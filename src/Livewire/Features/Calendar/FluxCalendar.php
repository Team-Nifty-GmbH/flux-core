<?php

namespace FluxErp\Livewire\Features\Calendar;

use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use FluxErp\Contracts\Calendarable;
use FluxErp\Facades\Action;
use FluxErp\Helpers\Helper;
use FluxErp\Livewire\Forms\CalendarEventForm;
use FluxErp\Models\Address;
use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use FluxErp\Models\User;
use FluxErp\Traits\HasCalendarUserSettings;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class FluxCalendar extends Component
{
    use Actions;

    #[Locked]
    public array $allCalendars = [];

    public array $calendarEvent = [];

    #[Locked]
    public bool $calendarEventWasRepeatable = false;

    #[Locked]
    public array $calendarPeriod = [
        'start' => null,
        'end' => null,
    ];

    public string $confirmDelete = 'this';

    public string $confirmSave = 'future';

    public CalendarEventForm $event;

    #[Locked]
    public array $oldCalendarEvent = [];

    public string $search = '';

    public array $searchResults = [];

    #[Locked]
    public array $selectableCalendars = [];

    #[Locked]
    public bool $showCalendars = true;

    #[Locked]
    public bool $showInvites = true;

    public string $tab = 'users';

    public array $validationErrors = [];

    protected $listeners = [
        'calendar-view-did-mount' => 'storeViewSettings',
        'calendar-toggle-event-source' => 'toggleEventSource',
    ];

    protected Collection $myCalendars;

    protected Collection $sharedWithMe;

    protected string $view = 'flux::livewire.features.calendar.flux-calendar';

    public function mount(): void
    {
        $this->calendarEvent = [
            'calendar_id' => null,
            'model_id' => null,
            'start' => now(),
            'end' => now(),
        ];
    }

    public function render(): View
    {
        return view($this->view);
    }

    #[Renderless]
    public function addInvitedRecord(int $id): void
    {
        $model = app($this->tab === 'users' ? User::class : Address::class);

        $this->addInvitee($model->query()->whereKey($id)->first());
    }

    #[Renderless]
    public function deleteEvent(array $attributes): array|false
    {
        $attributes['confirm_option'] = ! $this->calendarEventWasRepeatable ? 'all' : $this->confirmDelete;

        if ($attributes['calendar_type'] ?? false) {
            $action = Action::get($attributes['calendar_type'] . '.delete');

            if (! $action) {
                return false;
            }

            $modelClass = morphed_model($attributes['calendar_type']);

            try {
                $action['class']::make(resolve_static($modelClass, 'fromCalendarEvent', [$attributes]))
                    ->checkPermission()
                    ->validate()
                    ->execute();
            } catch (UnauthorizedException|ValidationException $e) {
                exception_to_notifications($e, $this);

                return false;
            }
        } else {
            try {
                $this->event->reset();
                $this->event->fill($attributes);
                $this->event->original_start = data_get($this->oldCalendarEvent, 'start');
                $this->event->delete();
            } catch (UnauthorizedException|ValidationException $e) {
                exception_to_notifications($e, $this);

                return false;
            }
        }

        return [
            'id' => $attributes['id'],
            'confirmOption' => $attributes['confirm_option'],
            'repetition' => $attributes['repetition'] ?? null,
        ];
    }

    #[Renderless]
    public function getCalendarEventsBeingListenedFor(): array
    {
        return array_intersect_key(
            method_exists(parent::class, 'getEventsBeingListenedFor')
                ? parent::getEventsBeingListenedFor()
                : array_keys(parent::getListeners()),
            [
                'select',
                'unselect',
                'dateClick',
                'eventClick',
                'eventMouseEnter',
                'eventMouseLeave',
                'eventDragStart',
                'eventDragStop',
                'eventDrop',
                'eventResizeStart',
                'eventResizeStop',
                'eventResize',
                'eventReceive',
                'eventLeave',
                'eventAdd',
                'eventChange',
                'eventRemove',
                'drop',
                'eventsSet',
            ]
        );
    }

    #[Renderless]
    public function getCalendarGroups(): array
    {
        return [
            'my' => __('My Calendars'),
            'shared' => __('Shared with me'),
            'public' => __('Public'),
            'other' => __('Other'),
        ];
    }

    #[Renderless]
    public function getCalendars(): array
    {
        $this->allCalendars = array_merge(
            $this->getMyCalendars()->toArray(),
            $this->getSharedWithMeCalendars()->toArray(),
            $this->getPublicCalendars()->toArray(),
            $this->getOtherCalendars()->toArray(),
        );

        $this->selectableCalendars = collect($this->allCalendars)
            ->where('resourceEditable', true)
            ->map(function ($calendar) {
                if ($parentId = data_get($calendar, 'parentId')) {
                    $calendar['name'] = resolve_static(Calendar::class, 'query')
                        ->query()
                        ->whereKey($parentId)
                        ->value('name') . ' -> ' . $calendar['name'] ?? '';
                }

                return $calendar;
            })
            ->all();

        return $this->allCalendars;
    }

    #[Renderless]
    public function getConfig(): array
    {
        return Arr::undot(
            array_merge(
                Arr::dot(
                    [
                        'locale' => app()->getLocale(),
                        'firstDay' => Carbon::getWeekStartsAt(),
                        'height' => 'auto',
                        'views' => $this->getViews(),
                        'headerToolbar' => [
                            'end' => 'prev,next today',
                            'left' => 'title',
                            'center' => 'timeGridDay,timeGridWeek,dayGridMonth',
                        ],
                        'nowIndicator' => true,
                        'buttonText' => [
                            'today' => __('Today'),
                            'month' => __('Month'),
                            'week' => __('Week'),
                            'day' => __('Day'),
                        ],
                    ]
                ),
                Arr::dot(auth()->user()->getCalendarSettings(static::class)->value('settings') ?? [])
            )
        );
    }

    #[Renderless]
    public function getEvents(array $info, array $calendarAttributes): array
    {
        if (data_get($calendarAttributes, 'hasNoEvents')) {
            return [];
        }

        if (($calendarAttributes['modelType'] ?? false)
            && data_get($calendarAttributes, 'isVirtual', false)
        ) {
            return resolve_static(morphed_model($calendarAttributes['modelType']), 'query')
                ->inTimeframe($info['start'], $info['end'], $calendarAttributes)
                ->get()
                ->map(fn (Model $model) => $model->toCalendarEvent($info))
                ->toArray();
        }

        $this->calendarPeriod = [
            'start' => Carbon::parse($info['startStr'])->toDateTimeString(),
            'end' => Carbon::parse($info['endStr'])->toDateTimeString(),
        ];

        $calendar = resolve_static(Calendar::class, 'query')
            ->whereKey($calendarAttributes['id'])
            ->first();

        $calendarEvents = $calendar->calendarEvents()
            ->whereNull('repeat')
            ->where(function ($query) use ($info): void {
                $query->whereBetween('start', [
                    Carbon::parse($info['start']),
                    Carbon::parse($info['end']),
                ])
                    ->orWhereBetween('end', [
                        Carbon::parse($info['start']),
                        Carbon::parse($info['end']),
                    ]);
            })
            ->with('invited', fn ($query) => $query->withPivot('status'))
            ->get()
            ->merge(
                $calendar->invitesCalendarEvents()
                    ->addSelect('calendar_events.*')
                    ->addSelect('inviteables.status')
                    ->addSelect('inviteables.model_calendar_id AS calendar_id')
                    ->whereIn('inviteables.status', ['accepted', 'maybe'])
                    ->get()
                    ->each(fn ($event) => $event->is_invited = true)
            );

        return $this->calculateRepeatableEvents($calendar, $calendarEvents)
            ->map(function ($event) use ($calendarAttributes, $calendar) {
                $invited = $this->getInvited($event);

                return $event->toCalendarEventObject([
                    'is_editable' => $calendarAttributes['permission'] !== 'reader',
                    'invited' => $invited,
                    'is_repeatable' => $calendar->has_repeatable_events ?? false,
                    'has_repeats' => ! is_null($event->repeat),
                ]);
            })
            ?->toArray();
    }

    public function getInvited(Model $event): array
    {
        return $event->invitedModels()
            ->map(
                function (Model $inviteable) {
                    return [
                        'id' => $inviteable->id,
                        'label' => $inviteable->getLabel(),
                        'pivot' => $inviteable->pivot,
                    ];
                }
            )
            ->toArray();
    }

    #[Renderless]
    public function getInvites(): array
    {
        return auth()->user()
            ->invites()
            ->with('calendarEvent:id,start,end,title,is_all_day,calendar_id')
            ->get()
            ->toArray();
    }

    #[Renderless]
    public function getMyCalendars(): Collection
    {
        resolve_static(
            Calendar::class,
            'addGlobalScope',
            [
                'scope' => fn (Builder $query) => $query->with('children'),
            ]
        );

        return $this->myCalendars = auth()->user()
            ->calendars()
            ->whereNull('parent_id')
            ->withPivot('permission')
            ->wherePivot('permission', 'owner')
            ->withCount('calendarables')
            ->get()
            ->toCalendarObjects();
    }

    #[Renderless]
    public function getOtherCalendars(): Collection
    {
        return collect(Relation::morphMap())
            ->filter(fn (string $modelClass) => in_array(Calendarable::class, class_implements($modelClass)))
            ->map(fn (string $modelClass) => resolve_static($modelClass, 'toCalendar'))
            ->flatMap(fn ($item) => Arr::isAssoc($item) ? [$item] : $item)
            ->values();
    }

    public function getPublicCalendars(): Collection
    {
        return resolve_static(Calendar::class, 'query')
            ->where('is_public', true)
            ->whereNotIn('id', $this->myCalendars->pluck('id'))
            ->whereNotIn('id', $this->sharedWithMe->pluck('id'))
            ->get()
            ->toFlatTree()
            ->map(function (Calendar $calendar) {
                return $calendar->toCalendarObject([
                    'permission' => 'reader',
                    'group' => 'public',
                    'resourceEditable' => false,
                ]);
            });
    }

    public function getSharedWithMeCalendars(): Collection
    {
        return $this->sharedWithMe = auth()->user()
            ->calendars()
            ->withPivot('permission')
            ->wherePivot('permission', '!=', 'owner')
            ->get()
            ->toFlatTree()
            ->map(function (Calendar $calendar) {
                return $calendar->toCalendarObject(
                    [
                        'permission' => $calendar['pivot']['permission'],
                        'resourceEditable' => $calendar['pivot']['permission'] !== 'reader',
                        'group' => 'shared',
                    ]
                );
            });
    }

    public function getViews(): array
    {
        return [
            'dayGridMonth',
        ];
    }

    #[Renderless]
    public function inviteStatus(Inviteable $event, string $status, int $calendarId): void
    {
        $event->status = $status;
        $event->model_calendar_id = $calendarId;
        $event->save();

        $this->skipRender();
    }

    public function isCalendarEventRepeatable(int|string $calendarId): bool
    {
        return (bool) resolve_static(Calendar::class, 'query')
            ->whereKey($calendarId)
            ->value('has_repeatable_events');
    }

    #[Renderless]
    public function onDateClick(array $eventInfo, ?array $calendar = null): void
    {
        if (! $calendar && $this->selectableCalendars) {
            $calendar = reset($this->selectableCalendars);
        }

        if (data_get($calendar, 'resourceEditable', false)) {
            $start = Carbon::parse($eventInfo['dateStr']);

            if ($start->format('H:i:s') === '00:00:00'
                && data_get($eventInfo, 'view.type') === 'dayGridMonth'
            ) {
                $now = now()->timezone(data_get($eventInfo, 'view.dateEnv.timeZone'));
                $start->timezone(data_get($eventInfo, 'view.dateEnv.timeZone'))
                    ->setHour($now->hour)
                    ->setMinute(now()->ceilMinute(15)->minute);
            }

            $this->onEventClick([
                'event' => [
                    'start' => $start->toDateTimeString(),
                    'end' => $start->addMinutes(15)->toDateTimeString(),
                    'allDay' => data_get($eventInfo, 'view.type') !== 'dayGridMonth'
                        ? data_get($eventInfo, 'allDay', false)
                        : false,
                    'calendar_id' => $calendar['id'] ?? null,
                    'model_type' => $calendar['modelType'] ?? null,
                    'model_id' => null,
                    'has_taken_place' => false,
                    'is_editable' => $calendar['resourceEditable'] ?? false,
                    'is_repeatable' => $calendar['hasRepeatableEvents'] ?? false,
                    'invited' => [],
                ],
            ]);
        }
    }

    #[Renderless]
    public function onEventClick(array $eventInfo): void
    {
        if ($exists = data_get($eventInfo, 'event.id', false)) {
            $this->selectableCalendars = array_filter(
                to_flat_tree($this->allCalendars),
                fn ($calendar) => data_get($calendar, 'modelType') ===
                    data_get($eventInfo, 'event.extendedProps.calendar_type')
            );
        }

        $this->calendarEvent = array_merge(
            [
                'interval' => null,
                'unit' => 'days',
                'weekdays' => [],
                'monthly' => null,
                'repeat_radio' => null,
                'repeat_end' => null,
                'recurrences' => null,
                'has_repeats' => false,
            ],
            Arr::pull($eventInfo['event'], 'extendedProps', []),
            $eventInfo['event']
        );

        if (data_get($this->calendarEvent, 'id')) {
            $explodedId = explode('|', $this->calendarEvent['id']);
            $this->calendarEvent['id'] = $explodedId[0];
            $this->calendarEvent['repetition'] = $explodedId[1] ?? null;
        }

        $this->oldCalendarEvent = $this->calendarEvent;

        $this->calendarEventWasRepeatable = $this->calendarEvent['has_repeats'] ?? false;
        $this->confirmSave = 'future';
        $this->confirmDelete = 'this';

        $this->showModal();

        if (data_get($eventInfo, 'event.calendar_id') && ! $exists) {
            $this->updatedCalendarEventCalendarId();
        }
    }

    #[Renderless]
    public function onEventDragStart(array $eventInfo): void {}

    #[Renderless]
    public function onEventDragStop(array $eventInfo): void {}

    #[Renderless]
    public function onEventDrop(array $eventInfo): void
    {
        $event = array_merge(
            Arr::pull($eventInfo['event'], 'extendedProps', []),
            $eventInfo['event'],
            [
                'has_repeats' => false,
            ]
        );
        $event['id'] = explode('|', data_get($event, 'id', ''))[0] ?: null;

        $this->oldCalendarEvent = array_merge(
            Arr::pull($eventInfo['oldEvent'], 'extendedProps', []),
            $eventInfo['oldEvent']
        );
        $this->calendarEventWasRepeatable = $this->oldCalendarEvent['has_repeats'] ?? false;
        $this->confirmSave = 'this';

        $this->saveEvent($event);
    }

    #[Renderless]
    public function removeSelectableCalendar(array $calendar): void
    {
        $this->selectableCalendars = collect($this->selectableCalendars)
            ->reject(fn ($item) => $item['id'] === data_get($calendar, 'id'))
            ->all();
    }

    #[Renderless]
    public function saveEvent(array $attributes): array|false
    {
        $attributes['is_all_day'] = $attributes['allDay'] ?? false;
        $attributes['confirm_option'] = ! $this->calendarEventWasRepeatable ? 'all' : $this->confirmSave;
        $attributes['calendar_type'] ??= data_get(
            collect($this->selectableCalendars)->firstWhere('id', data_get($attributes, 'calendar_id')),
            'model_type'
        );

        if ($attributes['has_repeats'] ?? false) {
            $attributes['repeat'] = [
                'start' => $attributes['start'],
                'interval' => $attributes['interval'] ?? null,
                'unit' => $attributes['unit'] ?? null,
                'weekdays' => $attributes['weekdays'] ?? null,
                'monthly' => $attributes['monthly'] ?? null,
            ];
        }

        if ($attributes['calendar_type'] ?? false) {
            $action = Action::get(
                $attributes['calendar_type'] . (($attributes['id'] ?? false) ? '.update' : '.create')
            );

            if (! $action) {
                return false;
            }

            $modelClass = morphed_model($attributes['calendar_type']);

            try {
                $result = $action['class']::make(resolve_static($modelClass, 'fromCalendarEvent', [$attributes]))
                    ->checkPermission()
                    ->validate()
                    ->execute();
            } catch (UnauthorizedException|ValidationException $e) {
                exception_to_notifications($e, $this);

                return false;
            }

            $result = $result->toCalendarEvent();
        } else {
            try {
                $attributes['extended_props'] = array_values(data_get($attributes, 'customProperties', []));
                $this->event->reset();
                $this->event->fill($attributes);
                $this->event->original_start = data_get($this->oldCalendarEvent, 'start');
                $this->event->save();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);

                return false;
            }

            $actionResult = $this->event->getActionResult();

            $result = match (true) {
                is_array($actionResult) => array_values(array_filter($actionResult)),
                default => Arr::wrap($actionResult),
            };
        }

        if (data_get($this->calendarPeriod, 'start') && data_get($this->calendarPeriod, 'end')) {
            $result = array_map(
                function ($event) use ($attributes) {
                    if ($event instanceof CalendarEvent) {
                        return $event->toCalendarEventObject([
                            'is_editable' => true,
                            'is_repeatable' => $attributes['is_repeatable'] ?? false,
                            'has_repeats' => ! is_null($event->repeat),
                        ]);
                    }

                    return $event;
                },
                Helper::getRepetitions($result, $this->calendarPeriod['start'], $this->calendarPeriod['end'])
            );
        }

        return $result ?: false;
    }

    #[Renderless]
    public function showModal(): void
    {
        $this->js(
            <<<'JS'
               $modalOpen('calendar-event-modal');
            JS
        );
    }

    #[Renderless]
    public function storeSettings(mixed $data, ?string $setPath = null): void
    {
        if (! in_array(HasCalendarUserSettings::class, class_uses_recursive(auth()->user()))) {
            return;
        }

        $currentData = auth()->user()->getCalendarSettings(static::class)->value('settings') ?? [];

        if (! is_null($setPath)) {
            data_set($currentData, $setPath, $data);
            $setData = $currentData;
        } else {
            $data = Arr::wrap($data instanceof Arrayable ? $data->toArray() : $data);
            $setData = Arr::undot(
                array_merge(
                    Arr::dot($currentData),
                    Arr::dot($data)
                )
            );
        }

        auth()->user()
            ->calendarUserSettings()
            ->updateOrCreate(
                [
                    'cache_key' => static::class,
                    'component' => static::class,
                ],
                [
                    'settings' => $setData,
                ]
            );
    }

    #[Renderless]
    public function storeViewSettings(array $view): void
    {
        $this->storeSettings(data_get($view, 'type'), 'initialView');
    }

    #[Renderless]
    public function toggleEventSource(array $activeCalendars): void
    {
        $this->storeSettings(array_column($activeCalendars, 'publicId'), 'activeCalendars');
    }

    public function updatedCalendarEventCalendarId(): void
    {
        $this->calendarEvent['customProperties'] = Arr::mapWithKeys(
            resolve_static(Calendar::class, 'query')
                ->whereKey($this->calendarEvent['calendar_id'])
                ->value('custom_properties') ?? [],
            fn ($item) => [$item['name'] => array_merge(['value' => null], $item)]
        );

        $this->skipRender();
    }

    #[Renderless]
    public function updatedCalendarEventStatus($value): void
    {
        $calendarEvent = resolve_static(CalendarEvent::class, 'query')
            ->whereKey($this->calendarEvent['id'])
            ->firstOrFail();

        $this->inviteStatus($calendarEvent, $value, $calendarEvent->calendar_id);
    }

    #[Renderless]
    public function updatedSearch(): void
    {
        $model = app($this->tab === 'users' ? User::class : Address::class);
        $this->searchResults = $this->search ? $model::search($this->search)->get()->toArray() : [];
    }

    #[Renderless]
    public function updatedTab(): void
    {
        $this->search = '';
        $this->searchResults = [];
    }

    #[Renderless]
    public function updateSelectableCalendars(array $calendar): void
    {
        $index = collect($this->selectableCalendars)->search(fn ($item) => $item['id'] === data_get($calendar, 'id'));
        if ($parentId = data_get($calendar, 'parentId')) {
            $calendar['name'] = resolve_static(Calendar::class, 'query')
                ->whereKey($parentId)
                ->value('name') . ' -> ' . $calendar['name'] ?? '';
        }

        if ($index === false) {
            $this->selectableCalendars[] = $calendar;
        } else {
            $this->selectableCalendars[$index] = $calendar;
        }
    }

    protected function calculateRepeatableEvents($calendar, Collection $calendarEvents): Collection
    {
        $repeatables = $calendar->calendarEvents()
            ->whereNotNull('repeat')
            ->whereDate('start', '<', $this->calendarPeriod['end'])
            ->where(fn ($query) => $query->whereDate('repeat_end', '>', $this->calendarPeriod['start'])
                ->orWhereNull('repeat_end')
            )
            ->get();

        foreach ($repeatables as $repeatable) {
            foreach ($this->calculateRepetitionsFromEvent($repeatable->toArray()) as $event) {
                $calendarEvents->push($event);
            }
        }

        return $calendarEvents;
    }

    protected function calculateRepetitionsFromEvent(array|Model $event): array
    {
        if (! ($repeatString = data_get($event, 'repeat'))) {
            return [
                $event instanceof Model ?
                    $event : app(CalendarEvent::class)->forceFill($event),
            ];
        }

        $i = 0;
        $events = [];
        $recurrences = data_get($event, 'recurrences');

        for ($j = count($repeatValues = explode(',', $repeatString)); $j > 0; $j--) {
            if (data_get($event, 'recurrences')) {
                if ($recurrences < 1) {
                    continue;
                }

                $datePeriod = new DatePeriod(
                    Carbon::parse(data_get($event, 'start')),
                    DateInterval::createFromDateString($repeatValues[$i]),
                    ($count = (int) ceil($recurrences / $j)) - (int) ($i === 0), // subtract 1, because start date does not count towards recurrences limit
                    (int) ($i !== 0) // 1 = Exclude start date
                );

                $recurrences -= $count;
            } else {
                $datePeriod = new DatePeriod(
                    Carbon::parse(data_get($event, 'start')),
                    DateInterval::createFromDateString($repeatValues[$i]),
                    Carbon::parse(is_null(data_get($event, 'repeat_end')) ?
                        $this->calendarPeriod['end'] :
                        min([data_get($event, 'repeat_end'), $this->calendarPeriod['end']])
                    ),
                    (int) ($i !== 0)
                );
            }

            // filter dates in between start and end
            $dates = array_filter(
                iterator_to_array($datePeriod),
                fn ($item) => ($date = $item->format('Y-m-d H:i:s')) > $this->calendarPeriod['start']
                    && $date < $this->calendarPeriod['end']
                    && ! in_array($date, data_get($event, 'excluded') ?: [])
                    && (
                        ! data_get($event, 'repeat_end')
                        || $date < Carbon::parse(data_get($event, 'repeat_end'))->toDateTimeString()
                    )
            );

            $events = array_merge($events, Arr::mapWithKeys($dates, function ($date, $key) use ($event) {
                $interval = date_diff(Carbon::parse(data_get($event, 'start')), Carbon::parse(data_get($event, 'end')));

                return [
                    $key => app(CalendarEvent::class)->forceFill(
                        array_merge(
                            $event,
                            [
                                'start' => ($start = Carbon::parse(data_get($event, 'start'))->setDateFrom($date))
                                    ->format('Y-m-d H:i:s'),
                                'end' => $start->add($interval)->format('Y-m-d H:i:s'),
                            ],
                            ['id' => data_get($event, 'id') . '|' . $key]
                        )
                    ),
                ];
            }));

            $i++;
        }

        return $events;
    }

    protected function getCacheKey(): string
    {
        return static::class;
    }

    #[Renderless]
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
    }
}
