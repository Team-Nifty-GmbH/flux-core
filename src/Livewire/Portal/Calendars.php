<?php

namespace FluxErp\Livewire\Portal;

use FluxErp\Livewire\Features\Calendar\Calendar as BaseCalendar;
use FluxErp\Models\CalendarEvent;

class Calendars extends BaseCalendar
{
    public function notAttendEvent(CalendarEvent $event): void
    {
        $event->invites()
            ->where('inviteable_type', auth()->user()->getMorphClass())
            ->where('inviteable_id', auth()->user()->getKey())
            ->delete();
    }
}
