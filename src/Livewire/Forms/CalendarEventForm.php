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

    public ?string $start = null;

    public ?string $end = null;

    public ?string $title = null;

    public ?string $description = null;

    public ?array $repeat = null;

    public ?string $repeat_end = null;

    public ?int $recurrences = null;

    public bool $is_all_day = false;

    public ?string $confirm_option = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateCalendarEvent::class,
            'update' => UpdateCalendarEvent::class,
            'delete' => DeleteCalendarEvent::class,
        ];
    }
}
