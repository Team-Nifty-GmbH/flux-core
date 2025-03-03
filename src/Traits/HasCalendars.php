<?php

namespace FluxErp\Traits;

use FluxErp\Models\Calendar;
use FluxErp\Models\Pivots\Inviteable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasCalendars
{
    public function calendars(): MorphToMany
    {
        return $this->morphToMany(Calendar::class, 'calendarable');
    }

    public function invites(): MorphMany
    {
        return $this->morphMany(Inviteable::class, 'inviteable');
    }
}
