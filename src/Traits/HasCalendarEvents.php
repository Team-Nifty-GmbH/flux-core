<?php

namespace FluxErp\Traits;

use FluxErp\Models\CalendarEvent;
use FluxErp\Models\Pivots\CalendarEventInvite;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasCalendarEvents
{
    public function calendarEvents(): MorphMany
    {
        return $this->morphMany(CalendarEvent::class, 'model_type');
    }

    public function calendarEventInvites(): MorphToMany
    {
        return $this->morphToMany(CalendarEvent::class, 'model', 'calendar_event_invites')
            ->using(CalendarEventInvite::class)
            ->withPivot(['status', 'model_calendar_id']);
    }
}
