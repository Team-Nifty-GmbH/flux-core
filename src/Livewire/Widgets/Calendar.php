<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Features\Calendar\FluxCalendar;
use FluxErp\Traits\Widgetable;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\Locked;

#[Isolate]
class Calendar extends FluxCalendar
{
    use Widgetable;

    #[Locked]
    public bool $showCalendars = false;

    #[Locked]
    public bool $showInvites = false;

    public function getConfig(): array
    {
        return array_merge(
            parent::getConfig(),
            [
                'initialView' => 'timeGridDay',
                'height' => 500,
            ]
        );
    }

    public static function getDefaultWidth(): int
    {
        return 2;
    }

    public static function getDefaultHeight(): int
    {
        return 2;
    }
}
