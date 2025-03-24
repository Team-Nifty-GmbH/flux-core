<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Inviteable extends FluxPivot
{
    public $table = 'inviteables';

    public function calendarEvent(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class);
    }

    public function inviteable(): MorphTo
    {
        return $this->morphTo('inviteable');
    }
}
