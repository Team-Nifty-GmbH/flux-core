<?php

namespace FluxErp\Livewire\Features\Calendar;

use FluxErp\Livewire\Forms\CalendarForm;
use FluxErp\Models\Calendar;
use FluxErp\Traits\HasCalendarEvents;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\Calendar\Livewire\CalendarOverview as TallCalendarOverview;
use TeamNiftyGmbH\DataTable\Helpers\ModelInfo;

class CalendarOverview extends TallCalendarOverview
{
    public CalendarForm $calendar;

    public array $availableModels = [];

    public array $fieldTypes = [];

    public function mount(): void
    {
        $this->selectedCalendar = app(Calendar::class)->toCalendarObject();

        $this->availableModels = model_info_all()
            ->filter(fn (ModelInfo $modelInfo) => $modelInfo->traits->contains(HasCalendarEvents::class))
            ->unique('morphClass')
            ->map(fn ($modelInfo) => [
                'label' => __(Str::headline($modelInfo->morphClass)),
                'value' => $modelInfo->morphClass,
            ])
            ->toArray();

        $this->fieldTypes = [
            'text' => __('Text'),
            'textarea' => __('Textarea'),
            'checkbox' => __('Checkbox'),
            'date' => __('Date'),
        ];

        // Todo: add 'select' and 'datetime' to available field types
    }

    public function render(): Factory|Application|View
    {
        return view('flux::livewire.features.calendar.calendar-overview');
    }

    public function saveCalendar(): array|false
    {
        if (data_get($this->selectedCalendar, 'isVirtual')) {
            return false;
        }

        try {
            $this->calendar->reset();
            $this->calendar->fill($this->selectedCalendar);
            $this->calendar->user_id = auth()->id();
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

    public function deleteCalendar(array $attributes): bool
    {
        $attributes['confirm_option'] = ! $this->calendarEventWasRepeatable ? 'all' : $this->confirmDelete;

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

    public function addCustomProperty(): void
    {
        $this->selectedCalendar['customProperties'][] = [
            'field_type' => null,
            'name' => null,
        ];
    }

    public function removeCustomProperty(int $index): void
    {
        unset($this->selectedCalendar['customProperties'][$index]);
    }
}
