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
