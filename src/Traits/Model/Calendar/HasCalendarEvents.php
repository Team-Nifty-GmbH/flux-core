<?php

namespace FluxErp\Traits\Model\Calendar;

use FluxErp\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCalendarEvents
{
    /**
     * @return MorphMany<CalendarEvent, $this>
     */
    public function calendarEvents(): MorphMany
    {
        return $this->morphMany(CalendarEvent::class, 'model');
    }
}
