<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\CalendarEventInvite;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;
use TeamNiftyGmbH\Calendar\Models\CalendarEvent as BaseCalendarEvent;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class CalendarEvent extends BaseCalendarEvent implements HasMedia
{
    use BroadcastsEvents, HasUserModification, InteractsWithMedia;

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function invited(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'inviteable')
            ->using(CalendarEventInvite::class)
            ->withPivot(['status', 'model_calendar_id']);
    }

    public function invitedAddresses(): MorphToMany
    {
        return $this->morphedByMany(Address::class, 'inviteable', 'inviteables')
            ->using(CalendarEventInvite::class)
            ->withPivot(['status', 'model_calendar_id']);
    }
}
