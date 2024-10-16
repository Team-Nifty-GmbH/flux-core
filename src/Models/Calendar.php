<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use TeamNiftyGmbH\Calendar\Models\Calendar as BaseCalendar;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class Calendar extends BaseCalendar
{
    use BroadcastsEvents, HasUserModification, LogsActivity, ResolvesRelationsThroughContainer;

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

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
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
