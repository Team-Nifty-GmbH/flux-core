<?php

namespace FluxErp\Livewire\Features\Calendar;

use DateInterval;
use DatePeriod;
use FluxErp\Contracts\Calendarable;
use FluxErp\Livewire\Forms\CalendarEventForm;
use FluxErp\Livewire\Forms\CalendarForm;
use FluxErp\Models\Calendar as CalendarModel;
use FluxErp\Models\CalendarEvent;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\Calendar\StoresCalendarSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class Calendar extends Component
{
    use Actions, StoresCalendarSettings;

    public CalendarForm $calendar;

    public ?array $calendarObject = null;

    #[Locked]
    public array $calendarPeriod = [
        'start' => null,
        'end' => null,
    ];

    public CalendarEventForm $event;

    public array $fieldTypes = [];

    public bool $showCalendars = true;

    public function mount(): void
    {
        $this->fieldTypes = [
            [
                'label' => __('Text'),
                'value' => 'text',
            ],
            [
                'label' => __('Textarea'),
                'value' => 'textarea',
            ],
            [
                'label' => __('Checkbox'),
                'value' => 'checkbox',
            ],
            [
                'label' => __('Date'),
                'value' => 'date',
            ],
        ];
    }

    public function render(): View
    {
        return view('flux::livewire.features.calendar.calendar');
    }

    public function addCustomProperty(): void
    {
        $this->calendar->custom_properties[] = [
            'field_type' => null,
            'name' => null,
        ];
    }

    #[Renderless]
    public function deleteCalendar(): bool
    {
        try {
            $this->calendar->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        return true;
    }

    #[Renderless]
    public function editCalendar(CalendarModel $calendar): void
    {
        $this->calendar->reset();
        $this->calendar->fill($calendar->toCalendarObject());

        $this->js(<<<'JS'
            $modalOpen('calendar-modal');
        JS);
    }

    #[Renderless]
    #[On('calendar-event-click')]
    #[On('calendar-event-change')]
    public function editEvent(array $event, ?string $trigger = null): void
    {
        if (
            (
                data_get($event, 'id')
                && ! data_get($event, 'extendedProps.is_editable')
            )
            || ! data_get($this->calendar, 'is_editable')
        ) {
            return;
        }

        $this->event->reset();
        $this->event->fillFromJs(array_merge(
            [
                'interval' => null,
                'unit' => 'days',
                'weekdays' => [],
                'monthly' => null,
                'repeat_radio' => null,
                'repeat_end' => null,
                'recurrences' => null,
                'has_repeats' => true,
            ],
            data_get($event, 'extendedProps', []),
            $event
        ));
        $this->event->original_start = data_get($event, 'start');

        if (data_get($this->event, 'id')) {
            $explodedId = explode('|', $this->event->id);
            $this->event->id = $explodedId[0];
            $this->event->repetition = $explodedId[1] ?? null;
        }

        if ($trigger === 'event-change') {
            try {
                $model = morphed_model(data_get($event, 'extendedProps.calendar_type') ?? '')
                    ?? resolve_static(CalendarEvent::class, 'class');
                $this->event->has_repeats = false;
                $this->event->save();

                $this->toast()
                    ->success(__(':model saved', ['model' => __(Str::headline(morph_alias($model)))]))
                    ->send();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);

                return;
            }
        } else {
            $this->js(
                <<<'JS'
                    $modalOpen('edit-event-modal');
                JS
            );
        }
    }

    #[Renderless]
    public function getCalendars(): array
    {
        return collect(
            [
                [
                    'id' => 'my-calendars',
                    'name' => __('My calendars'),
                    'label' => __('My calendars'),
                    'hasNoEvents' => true,
                    'children' => auth()->user()
                        ?->calendars()
                        ->withPivot('permission')
                        ->wherePivot('permission', 'owner')
                        ->whereNull('parent_id')
                        ->get()
                        ->toCalendarObjects()
                        ->toArray(),
                ],
                [
                    'id' => 'shared-with-me',
                    'name' => __('Shared with me'),
                    'label' => __('Shared with me'),
                    'hasNoEvents' => true,
                    'children' => auth()->user()
                        ?->calendars()
                        ->withPivot('permission')
                        ->wherePivot('permission', '!=', 'owner')
                        ->get()
                        ->toFlatTree()
                        ->map(function (CalendarModel $calendar) {
                            return $calendar->toCalendarObject(
                                [
                                    'permission' => data_get($calendar, 'pivot.permission'),
                                    'resourceEditable' => data_get($calendar, 'pivot.permission') !== 'reader',
                                    'group' => 'shared',
                                ]
                            );
                        })
                        ->toArray(),
                ],
                [
                    'id' => 'public',
                    'name' => __('Public'),
                    'label' => __('Public'),
                    'hasNoEvents' => true,
                    'children' => resolve_static(CalendarModel::class, 'familyTree')
                        ->where('is_public', true)
                        ->whereDoesntHave('calendarables', function (Builder $query): void {
                            $query->where('calendarable_type', auth()->user()?->getMorphClass())
                                ->where('calendarable_id', auth()->id())
                                ->where('permission', 'owner');
                        })
                        ->get()
                        ->map(function (CalendarModel $calendar) {
                            return $calendar->toCalendarObject([
                                'permission' => 'reader',
                                'group' => 'public',
                                'resourceEditable' => false,
                            ]);
                        })
                        ->toArray(),
                ],
                [
                    'id' => 'other',
                    'name' => __('Other'),
                    'label' => __('Other'),
                    'hasNoEvents' => true,
                    'children' => collect(Relation::morphMap())
                        ->filter(
                            fn (string $modelClass) => in_array(Calendarable::class, class_implements($modelClass))
                        )
                        ->map(fn (string $modelClass) => resolve_static($modelClass, 'toCalendar'))
                        ->flatMap(fn ($item) => Arr::isAssoc($item) ? [$item] : $item)
                        ->values()
                        ->toArray(),
                ],
            ],
        )
            ->filter(fn (array $item) => data_get($item, 'children'))
            ->values()
            ->toArray();
    }

    #[Renderless]
    public function getConfig(): array
    {
        return Arr::undot(
            array_merge(
                Arr::dot(
                    [
                        'locale' => app()->getLocale(),
                        'timeZone' => auth()->user()?->timezone ?? 'local',
                        'firstDay' => Carbon::getWeekStartsAt(),
                        'height' => '500px',
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
                Arr::dot(
                    auth()
                        ->user()
                        ?->getCalendarSettings(static::class)
                        ->value('settings') ?? []
                )
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
            return $this->getCalendarEventsFromModelTypeQuery(
                $calendarAttributes['modelType'],
                $info['start'],
                $info['end'],
                $calendarAttributes
            )
                ->get()
                ->map(fn (Model $model) => $model->toCalendarEvent($info))
                ->toArray();
        }

        $this->calendarPeriod = [
            'start' => Carbon::parse($info['startStr'])->toDateTimeString(),
            'end' => Carbon::parse($info['endStr'])->toDateTimeString(),
        ];

        $calendar = resolve_static(CalendarModel::class, 'query')
            ->whereKey($calendarAttributes['id'])
            ->first();

        $calendarEvents = $calendar->calendarEvents()
            ->whereNull('repeat')
            ->where(function ($query) use ($info): void {
                $query->where('start', '<=', Carbon::parse($info['end']))
                    ->where('end', '>=', Carbon::parse($info['start']));
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
    public function getInvites(): ?array
    {
        return auth()->user()
            ?->invites()
            ->with('calendarEvent:id,start,end,title,is_all_day,calendar_id')
            ->get()
            ->toArray();
    }

    public function removeCustomProperty(int $index): void
    {
        unset($this->calendar->custom_properties[$index]);
    }

    #[Renderless]
    public function saveCalendar(): bool
    {
        $isNew = ! $this->calendar->id;
        try {
            $this->calendar->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->calendarObject = $this->calendar
            ->getActionResult()
            ->toCalendarObject(['isNew' => $isNew]);

        $this->toast()
            ->success(__(':model saved', ['model' => __('Calendar')]))
            ->send();

        return true;
    }

    #[Renderless]
    #[On('calendar-date-click')]
    public function timeslotClick(bool $allDay, string $dateStr, array $view): void
    {
        if (! $this->calendar->is_editable) {
            return;
        }

        $start = Carbon::parse($dateStr);

        if ($start->format('H:i:s') === '00:00:00'
            && data_get($view, 'type') === 'dayGridMonth'
        ) {
            $now = now()->timezone(data_get($view, 'dateEnv.timeZone'));
            $start->timezone(data_get($view, 'dateEnv.timeZone'))
                ->setHour($now->hour)
                ->setMinute(now()->ceilMinute(15)->minute);
        }

        $this->editEvent([
            'start' => $start->toIso8601String(),
            'end' => $start->addMinutes(15)->toIso8601String(),
            'allDay' => $allDay,
            'calendar_id' => $this->calendar->id,
            'model_type' => $this->calendar->model_type,
            'model_id' => null,
            'has_taken_place' => false,
            'is_editable' => $this->calendar->is_editable,
            'is_repeatable' => $this->calendar->has_repeatable_events,
            'has_repeats' => false,
            'invited' => [],
        ]);
    }

    public function updatedCalendarObject(): void
    {
        $this->calendar->reset();
        $this->calendar->fill($this->calendarObject ?? []);
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
                $interval = date_diff(
                    Carbon::parse(data_get($event, 'start')),
                    Carbon::parse(data_get($event, 'end'))
                );

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

    protected function getCalendarEventsFromModelTypeQuery(
        string $modelType,
        string $start,
        string $end,
        array $calendarAttributes
    ): Builder {
        return resolve_static(morphed_model($modelType), 'query')
            ->inTimeframe($start, $end, $calendarAttributes);
    }

    protected function getViews(): array
    {
        return [
            'dayGridMonth',
        ];
    }
}
