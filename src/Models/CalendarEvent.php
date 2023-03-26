<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\CalendarEventInvite;
use FluxErp\Traits\BroadcastsEvents;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;

class CalendarEvent extends Model implements HasMedia
{
    use BroadcastsEvents, HasPackageFactory, HasUserModification, HasUuid, InteractsWithMedia;

    protected $with = [
        'calendar',
    ];

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'is_all_day' => 'boolean',
    ];

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function invitedUsers(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'model', 'calendar_event_invites')
            ->using(CalendarEventInvite::class)
            ->withPivot(['status', 'model_calendar_id']);
    }

    public function invitedAddresses(): MorphToMany
    {
        return $this->morphedByMany(Address::class, 'model', 'calendar_event_invites')
            ->using(CalendarEventInvite::class)
            ->withPivot(['status', 'model_calendar_id']);
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model');
    }
}
