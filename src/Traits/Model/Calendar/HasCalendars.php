<?php

namespace FluxErp\Traits\Model\Calendar;

use FluxErp\Models\Calendar;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasCalendars
{
    public function calendars(): MorphToMany
    {
        return $this->morphToMany(Calendar::class, 'calendarable', 'calendarable');
    }
}
