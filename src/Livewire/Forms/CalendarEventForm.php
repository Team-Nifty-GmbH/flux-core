<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\CalendarEvent\CreateCalendarEvent;
use FluxErp\Actions\CalendarEvent\DeleteCalendarEvent;
use FluxErp\Actions\CalendarEvent\UpdateCalendarEvent;
use Livewire\Attributes\Locked;

class CalendarEventForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    #[Locked]
    public ?string $calendar_type = null;

    #[Locked]
    public ?int $calendar_id = null;

    public ?string $starts_at = null;

    public ?string $ends_at = null;

    public ?string $status = null;

    public bool $is_all_day = false;

    protected function getActions(): array
    {
        return [
            'create' => CreateCalendarEvent::class,
            'update' => UpdateCalendarEvent::class,
            'delete' => DeleteCalendarEvent::class,
        ];
    }
}
