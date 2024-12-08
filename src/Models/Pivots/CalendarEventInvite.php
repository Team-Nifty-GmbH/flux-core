<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\CalendarEvent;
use FluxErp\Traits\ResolvesRelationsThroughContainer;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class CalendarEventInvite extends MorphPivot
{
    use BroadcastsEvents, ResolvesRelationsThroughContainer;

    protected $table = 'calendar_event_invites';

    public function authenticatable(): MorphTo
    {
        return $this->morphTo('model');
    }

    public function calendarEvent(): BelongsTo
    {
        return $this->belongsTo(CalendarEvent::class);
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

    public function notifiable(): MorphTo
    {
        return $this->authenticatable();
    }
}
