<?php

namespace FluxErp\Livewire\Features\Calendar;

use DragonCode\Contracts\Support\Arrayable;
use FluxErp\Contracts\Calendarable;
use FluxErp\Facades\Action;
use FluxErp\Helpers\Helper;
use FluxErp\Livewire\Forms\CalendarEventForm;
use FluxErp\Models\Address;
use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use FluxErp\Models\User;
use FluxErp\Traits\HasCalendarUserSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\Calendar\Livewire\CalendarComponent;

class FluxCalendar extends CalendarComponent
{
    public string $tab = 'users';

    public string $search = '';

    public array $searchResults = [];

    public CalendarEventForm $event;

    protected string $view = 'flux::livewire.features.calendar.flux-calendar';

    protected $listeners = [
        'calendar-view-did-mount' => 'storeViewSettings',
        'calendar-toggle-event-source' => 'toggleEventSource',
    ];

    protected function getCacheKey(): string
    {
        return static::class;
    }

    #[Renderless]
    public function toggleEventSource(array ...$activeCalendars): void
    {
        $this->storeSettings(array_column($activeCalendars, 'publicId'), 'activeCalendars');
    }

    #[Renderless]
    public function storeViewSettings(array $view): void
    {
        $this->storeSettings(data_get($view, 'type'), 'initialView');
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
    public function getConfig(): array
    {
        return Arr::undot(
            array_merge(
                Arr::dot(parent::getConfig()),
                Arr::dot(auth()->user()->getCalendarSettings(static::class)->value('settings') ?? [])
            )
        );
    }

    #[Renderless]
    public function getOtherCalendars(): Collection
    {
        $calendarables = collect(Relation::morphMap())
            ->filter(fn (string $modelClass) => in_array(Calendarable::class, class_implements($modelClass)))
            ->map(fn (string $modelClass) => resolve_static($modelClass, 'toCalendar'))
            ->flatMap(fn ($item) => Arr::isAssoc($item) ? [$item] : $item)
            ->values();

        return parent::getOtherCalendars()->isEmpty() ?
            $calendarables->merge(parent::getOtherCalendars())
            : parent::getOtherCalendars()->merge($calendarables);
    }

    #[Renderless]
    public function getEvents(array $info, array $calendarAttributes): array
    {
        if (($calendarAttributes['modelType'] ?? false)
            && data_get($calendarAttributes, 'isVirtual', false)
        ) {
            return morphed_model($calendarAttributes['modelType'])::query()
                ->inTimeframe($info['start'], $info['end'])
                ->get()
                ->map(fn (Model $model) => $model->toCalendarEvent())
                ->toArray();
        }

        return parent::getEvents($info, $calendarAttributes);
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

        return $result ?: false;
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
    public function addInvitedRecord(int $id): void
    {
        $model = app($this->tab === 'users' ? User::class : Address::class);

        $this->addInvitee($model->query()->whereKey($id)->first());
    }

    #[Renderless]
    public function onEventClick(array $eventInfo): void
    {
        if ($exists = data_get($eventInfo, 'event.id', false)) {
            $this->selectableCalendars = array_filter(
                $this->allCalendars,
                fn ($calendar) => data_get($calendar, 'modelType') ===
                    data_get($eventInfo, 'event.extendedProps.calendar_type')
            );
        }

        parent::onEventClick($eventInfo);

        if (data_get($eventInfo, 'event.calendar_id') && ! $exists) {
            $this->updatedCalendarEventCalendarId();
        }
    }

    public function render(): View
    {
        return view($this->view);
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
