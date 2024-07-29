<?php

namespace FluxErp\Actions\CalendarEvent;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\CalendarEvent;
use FluxErp\Rulesets\CalendarEvent\SyncCalendarEventInvitesRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class SyncCalendarEventInvites extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(SyncCalendarEventInvitesRuleset::class, 'getRules');
    }

    public static function name(): string
    {
        return 'calendar-event.sync-invites';
    }

    public static function models(): array
    {
        return [CalendarEvent::class];
    }

    public function performAction(): Model
    {
        $calendarEvent = resolve_static(CalendarEvent::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        if (array_key_exists('invited_addresses', $this->data)) {
            $invitedAddresses = Arr::keyBy($this->data['invited_addresses'], 'id');
            $invitedAddresses = Arr::map($invitedAddresses, function ($value) {
                return Arr::only($value, ['status']);
            });

            $calendarEvent->invitedAddresses()->sync($invitedAddresses);
        }

        if (array_key_exists('invited', $this->data)) {
            $invitedUsers = Arr::keyBy($this->data['invited'], 'id');
            $invitedUsers = Arr::map($invitedUsers, function ($value) {
                return Arr::only($value, ['status']);
            });

            $calendarEvent->invited()->sync($invitedUsers);
        }

        return $calendarEvent;
    }
}
