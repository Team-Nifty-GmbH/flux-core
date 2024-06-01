<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Calendar\CreateCalendar;
use FluxErp\Actions\Calendar\DeleteCalendar;
use FluxErp\Actions\Calendar\UpdateCalendar;
use Livewire\Attributes\Locked;

class CalendarForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public ?string $description = null;

    public ?string $color = null;

    public bool $has_notifications = true;

    public bool $is_editable = true;

    public bool $is_public = false;

    public ?int $user_id = null;

    protected function getActions(): array
    {
        return [
            'create' => CreateCalendar::class,
            'update' => UpdateCalendar::class,
            'delete' => DeleteCalendar::class,
        ];
    }
}
