<?php

namespace FluxErp\Livewire\Lead;

use FluxErp\Livewire\Features\Calendar\Calendar as BaseCalendar;
use FluxErp\Models\Lead;
use FluxErp\Models\Task;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;

class Calendar extends BaseCalendar
{
    #[Modelable]
    public ?int $leadId = null;

    #[Renderless]
    #[On('calendar-event-click')]
    #[On('calendar-event-change')]
    public function editEvent(array $event, ?string $trigger = null): void
    {
        parent::editEvent($event, $trigger);

        $this->event->model_type = morph_alias(Lead::class);
        $this->event->model_id = $this->leadId;
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
                    'id' => 'other',
                    'name' => __('Other'),
                    'label' => __('Other'),
                    'hasNoEvents' => true,
                    'children' => [
                        resolve_static(Task::class, 'toCalendar'),
                    ],
                ],
            ]
        )
            ->filter(fn (array $item) => data_get($item, 'children'))
            ->values()
            ->toArray();
    }

    protected function getCalendarEventsFromModelTypeQuery(
        string $modelType,
        string $start,
        string $end,
        array $calendarAttributes
    ): Builder {
        return parent::getCalendarEventsFromModelTypeQuery($modelType, $start, $end, $calendarAttributes)
            ->whereHasMorph(
                'model',
                [Lead::class],
                fn (Builder $query) => $query->whereKey($this->leadId)
            );
    }
}
