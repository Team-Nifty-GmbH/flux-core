<?php

namespace FluxErp\Livewire\Features\Calendar;

use FluxErp\Livewire\Forms\CalendarForm;
use FluxErp\Models\Calendar;
use FluxErp\Traits\HasCalendarEvents;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class CalendarOverview extends Component
{
    use Actions;

    public array $availableModels = [];

    public CalendarForm $calendar;

    #[Locked]
    public array $calendarGroups = [];

    public array $fieldTypes = [];

    public array $parentCalendars = [];

    public array $selectedCalendar;

    public bool $showCalendars = true;

    public bool $showInvites = true;

    public function mount(): void
    {
        $this->selectedCalendar = app(Calendar::class)->toCalendarObject();

        $this->availableModels = model_info_all()
            ->filter(fn (ModelInfo $modelInfo) => in_array(
                HasCalendarEvents::class,
                class_uses_recursive($modelInfo->class)
            ))
            ->unique('morphClass')
            ->map(fn ($modelInfo) => [
                'label' => __(Str::headline($modelInfo->morphClass)),
                'value' => $modelInfo->morphClass,
            ])
            ->toArray();

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

        // Todo: add 'select', 'datetime', 'toggle' to available field types
    }

    public function render(): Factory|Application|View
    {
        return view('flux::livewire.features.calendar.calendar-overview');
    }

    public function addCustomProperty(): void
    {
        $this->selectedCalendar['customProperties'][] = [
            'field_type' => null,
            'name' => null,
        ];
    }

    public function deleteCalendar(array $attributes): bool
    {
        try {
            $this->calendar->reset();
            $this->calendar->fill($attributes);
            $this->calendar->delete();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        return true;
    }

    public function editCalendar(?array $calendar = null): void
    {
        if (is_null($calendar)) {
            $calendar = app(Calendar::class)
                ->toCalendarObject();

            $calendar['color'] = '#' . dechex(rand(0x000000, 0xFFFFFF));
        }

        $this->selectedCalendar = $calendar;

        $this->parentCalendars = $this->getAvailableParents();

        $this->js(
            <<<'JS'
                $modalOpen('calendar-modal');
            JS
        );
    }

    public function removeCustomProperty(int $index): void
    {
        unset($this->selectedCalendar['customProperties'][$index]);
    }

    public function saveCalendar(): array|false
    {
        if (data_get($this->selectedCalendar, 'isVirtual')) {
            return false;
        }

        try {
            $this->calendar->reset();
            $this->calendar->fill($this->selectedCalendar);

            if ($this->calendar->model_type) {
                $this->calendar->user_id = null;
            } else {
                $this->calendar->user_id = auth()->id();
            }

            $this->calendar->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $result = $this->calendar->getActionResult();

        $result = $result?->toCalendarObject([
            'resourceEditable' => data_get($result, 'is_editable', false),
            'hasRepeatableEvents' => data_get($result, 'has_repeatable_events', false),
        ]);

        if (! $result) {
            return false;
        }

        return $result;
    }

    protected function getAvailableParents(): array
    {
        return resolve_static(Calendar::class, 'query')
            ->whereKeyNot(data_get($this->selectedCalendar, 'id'))
            ->whereNull('parent_id')
            ->when(
                data_get($this->selectedCalendar, 'id'),
                fn ($query) => $query->where('model_type', data_get($this->selectedCalendar, 'modelType'))
            )
            ->get(['id', 'name', 'description'])
            ->toArray();
    }
}
