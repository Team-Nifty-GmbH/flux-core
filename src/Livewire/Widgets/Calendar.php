<?php

namespace FluxErp\Livewire\Widgets;

use FluxErp\Livewire\Features\Calendar\Calendar as BaseCalendar;
use FluxErp\Traits\Widgetable;
use Illuminate\Support\Arr;
use Livewire\Attributes\Isolate;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;

#[Isolate]
class Calendar extends BaseCalendar
{
    use Widgetable;

    #[Locked]
    public bool $showCalendars = false;

    #[Locked]
    public bool $showInvites = false;

    public static function getDefaultHeight(): int
    {
        return 2;
    }

    public static function getDefaultWidth(): int
    {
        return 2;
    }

    #[Renderless]
    public function getConfig(): array
    {
        return array_merge(
            parent::getConfig(),
            Arr::undot(
                array_merge(
                    auth()->user()->getCalendarSettings(static::class)->value('settings') ?? [],
                    [
                        'showCalendars' => $this->showCalendars,
                        'showInvites' => $this->showInvites,
                    ]
                )
            )
        );
    }
}
