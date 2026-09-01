<?php

namespace FluxErp\Livewire\Forms;

use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use FluxErp\Actions\DispatchableFluxAction;
use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use FluxErp\Support\Livewire\Attributes\ExcludeFromActionData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class CalendarEventForm extends FluxForm
{
    // Model properties
    public string|int|null $id = null;

    public int|string|null $calendar_id = null;

    public ?string $model_type = null;

    public ?int $model_id = null;

    public ?string $start = null;

    public ?string $end = null;

    public ?string $title = null;

    public ?string $description = null;

    public ?array $repeat = [
        'interval' => 1,
        'unit' => 'days',
        'weekdays' => [],
        'monthly' => 'day',
        'repeat_radio' => null,
    ];

    public ?string $repeat_end = null;

    public ?int $recurrences = null;

    public bool $has_taken_place = false;

    public bool $is_all_day = false;

    public ?array $extended_props = null;

    // Non model properties
    public ?string $calendar_type = null;

    public string $confirm_option = 'this';

    public ?string $edit_component = null;

    public ?string $original_start = null;

    public ?string $status = null;

    public ?int $repetition = null;

    public bool $has_repeats = false;

    public bool $is_cancelled = false;

    public bool $is_editable = true;

    public bool $is_repeatable = false;

    public bool $was_repeatable = false;

    public ?array $model = null;

    #[ExcludeFromActionData]
    public array $calendar = [];

    public function cancel(): void
    {
        $action = $this->makeAction('cancel')
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->validate();

        if ($this->asyncAction && ! $action instanceof DispatchableFluxAction) {
            throw new InvalidArgumentException('Async actions must be DispatchableFluxAction');
        }

        if ($this->asyncAction) {
            $action->executeAsync();
            $this->reset();

            return;
        }

        $response = $action->execute();

        $this->actionResult = $response;

        $this->reset();
    }

    public function fill($values): void
    {
        if ($values instanceof Model) {
            $model = $values->model;
            $values = $values->toArray();
        } else {
            $model = resolve_static(CalendarEvent::class, 'query')
                ->whereKey(data_get($values, 'id'))
                ->with(['model'])
                ->first(['model_type', 'model_id'])
                ?->model;
        }

        if ($model && method_exists($model, 'getUrl')) {
            $this->model = [
                'label' => $model->getLabel(),
                'url' => $model->getUrl(),
            ];
        }

        $wasRepeatable = false;
        if (is_string(data_get($values, 'repeat'))) {
            $values['repeat'] = Helper::parseRepeatStringToArray(data_get($values, 'repeat'));
            $wasRepeatable = true;
        } elseif (str_contains(data_get($values, 'id', ''), '|')) {
            $wasRepeatable = true;
        }

        parent::fill($values);

        $this->end ??= $this->start;
        $this->was_repeatable = $wasRepeatable;

        if (! is_array($this->repeat)) {
            $this->reset('repeat');
        }
    }

    public function fillFromJs(array $values): void
    {
        if (
            $calendarId = data_get($values, 'extendedProps.calendar_id') ?? data_get($values, 'calendar_id')
        ) {
            $values['calendar'] = resolve_static(Calendar::class, 'query')
                ->whereKey($calendarId)
                ->first()
                ?->toCalendarObject() ?? [];

            if (is_null(data_get($values, 'id')) && is_array(data_get($values, 'calendar.customProperties'))) {
                data_set(
                    $values,
                    'extendedProps.customProperties',
                    array_map(
                        function (array $item) {
                            $item['value'] = null;

                            return $item;
                        },
                        data_get($values, 'calendar.customProperties')
                    )
                );
            }
        } else {
            unset($values['calendar']);
        }

        if (data_get($values, 'allDay')) {
            if (is_null(data_get($values, 'end'))) {
                $values['end'] = $values['start'];
            } elseif (! Carbon::parse(data_get($values, 'end'))
                ->isSameDay(Carbon::parse(data_get($values, 'start')))
            ) {
                $values['end'] = Carbon::parse(data_get($values, 'end'))
                    ->subDay()
                    ->toDateString();
            }
        }

        $values['is_all_day'] = data_get($values, 'allDay');

        $values['repeat'] = [
            'interval' => Arr::pull($values, 'interval'),
            'unit' => Arr::pull($values, 'unit'),
            'weekdays' => Arr::pull($values, 'weekdays'),
            'monthly' => Arr::pull($values, 'monthly'),
            'repeat_radio' => Arr::pull($values, 'repeat_radio'),
        ];

        $values['extended_props'] = Arr::pull($values, 'extendedProps.customProperties');

        $this->fill($values);
    }

    public function reactivate(): void
    {
        $action = $this->makeAction('reactivate')
            ->when($this->checkPermission, fn (FluxAction $action) => $action->checkPermission())
            ->validate();

        if ($this->asyncAction && ! $action instanceof DispatchableFluxAction) {
            throw new InvalidArgumentException('Async actions must be DispatchableFluxAction');
        }

        if ($this->asyncAction) {
            $action->executeAsync();
            $this->reset();

            return;
        }

        $response = $action->execute();

        $this->actionResult = $response;

        $this->reset();
    }

    public function save(): void
    {
        if ($this->was_repeatable
            && $this->has_repeats
            && $this->confirm_option === 'this'
        ) {
            $this->confirm_option = 'future';
        }

        if (! $this->was_repeatable) {
            $this->confirm_option = 'all';
        }

        parent::save();
    }

    protected function getActions(): array
    {
        return [];
    }

    protected function makeAction(string $name, ?array $data = null): FluxAction
    {
        $model = morphed_model($this->calendar_type ?? '')
            ?? resolve_static(CalendarEvent::class, 'class');

        $data = $this->toArray();
        if (! data_get($data, 'is_repeatable') || ! data_get($data, 'has_repeats')) {
            unset($data['repeat']);
        }

        $dateProperties = [
            'start',
            'end',
            'repeat_end',
            'original_start',
        ];

        foreach ($dateProperties as $property) {
            if ($value = data_get($data, $property)) {
                try {
                    $data[$property] = Carbon::parse($value)->timezone('UTC')->toDateTimeString();
                } catch (InvalidFormatException) {
                    //
                }
            }
        }

        return $model::fromCalendarEvent($data, $name);
    }
}
