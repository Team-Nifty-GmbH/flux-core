<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Features\Calendar\FluxCalendar;
use FluxErp\Traits\Widgetable;
use Illuminate\Support\Arr;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;

#[Isolate]
class Calendar extends FluxCalendar
{
    use Widgetable;

    #[Locked]
    public bool $showCalendars = false;

    #[Locked]
    public bool $showInvites = false;

    #[Renderless]
    public function getConfig(): array
    {
        return array_merge(
            parent::getConfig(),
            Arr::undot(
                array_merge(
                    auth()->user()->getCalendarSettings(static::class)->value('settings'),
                    [
                        'showCalendars' => $this->showCalendars,
                        'showInvites' => $this->showInvites,
                    ]
                )
            )
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
