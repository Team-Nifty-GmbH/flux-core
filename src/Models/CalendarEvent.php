<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\CalendarEventInvite;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\ResolvesRelationsThroughContainer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;
use Spatie\MediaLibrary\HasMedia;
use TeamNiftyGmbH\Calendar\Models\CalendarEvent as BaseCalendarEvent;
use TeamNiftyGmbH\DataTable\Traits\BroadcastsEvents;

class CalendarEvent extends BaseCalendarEvent implements HasMedia
{
    use BroadcastsEvents, HasUserModification, InteractsWithMedia, LogsActivity, ResolvesRelationsThroughContainer;

    protected $guarded = [
        'id',
    ];

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

    public function toCalendarEventObject(array $attributes = []): array
    {
        $attributes = array_merge(
            [
                'calendar_type' => $this->calendar()->value('model_type'),
                'extendedProps' => array_filter($this->extended_props ?? [], fn ($item) => ! is_array($item)),
            ],
            $attributes
        );

        $customProperties = array_map(
            fn ($item) => array_merge($item, ['value' => null]),
            $this->calendar()->value('custom_properties') ?? []
        );

        $calendarEventObject = parent::toCalendarEventObject($attributes);
        $calendarEventObject['customProperties'] = Arr::keyBy(
            array_filter($this->extended_props ?? [], fn ($item) => is_array($item)),
            'name'
        );

        foreach ($customProperties as $customProperty) {
            if (! array_key_exists($customProperty['name'], $calendarEventObject['customProperties'])) {
                $calendarEventObject['customProperties'][$customProperty['name']] = $customProperty;
            }
        }

        return $calendarEventObject;
    }
}
