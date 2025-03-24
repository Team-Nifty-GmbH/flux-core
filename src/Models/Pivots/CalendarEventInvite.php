<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\CalendarEvent;
use FluxErp\Traits\ResolvesRelationsThroughContainer;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CalendarEventInvite extends MorphPivot
{
    use ResolvesRelationsThroughContainer;

    protected $table = 'calendar_event_invites';

    public function authenticatable(): MorphTo
    {
        return $this->morphTo('model');
    }

    /**
     * Get the channels that model events should broadcast on.
     *
     * @param  string  $event
     */
    public function broadcastOn($event): PrivateChannel
    {
        return new PrivateChannel(
            str_replace('\\', '.', $this->model_type) . '.' . $this->model_id
        );
    }

    public function calendarEvent(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->authenticatable();
    }
}
