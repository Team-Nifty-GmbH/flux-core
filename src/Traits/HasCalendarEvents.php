<?php

namespace FluxErp\Traits;

use FluxErp\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCalendarEvents
{
    public function calendarEvents(): MorphMany
    {
        return $this->morphMany(CalendarEvent::class, 'model');
    }
}
