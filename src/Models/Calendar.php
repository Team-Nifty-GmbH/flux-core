<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasUserModification;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use TeamNiftyGmbH\Calendar\Models\Calendar as BaseCalendar;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class Calendar extends BaseCalendar
{
    use BroadcastsEvents, HasUserModification;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::deleting(function ($calendar) {
            $calendar->calendarEvents()->delete();
        });
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Calendar::class, foreignKey: 'parent_id', localKey: 'id')
            ->with('children');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Calendar::class, 'parent_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
