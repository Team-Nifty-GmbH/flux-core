<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use TeamNiftyGmbH\Calendar\Models\Calendar as BaseCalendar;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class Calendar extends BaseCalendar
{
    use BroadcastsEvents, HasUserModification, LogsActivity;

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::deleting(function ($calendar) {
            $calendar->calendarEvents()->delete();
        });
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'calendarable', 'calendarables');
    }
}
