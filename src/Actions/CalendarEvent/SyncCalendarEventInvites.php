<?php

namespace FluxErp\Actions\CalendarEvent;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateCalendarEventRequest;
use FluxErp\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class SyncCalendarEventInvites extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = array_filter(
            (new UpdateCalendarEventRequest())->rules(),
            fn ($key) => str_starts_with($key, 'invited_') || $key === 'id',
            ARRAY_FILTER_USE_KEY
        );
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
        $calendarEvent = CalendarEvent::query()
            ->whereKey($this->data['id'])
            ->first();

        if (array_key_exists('invited_addresses', $this->data)) {
            $invitedAddresses = Arr::keyBy($this->data['invited_addresses'], 'id');
            $invitedAddresses = Arr::map($invitedAddresses, function ($value) {
                return Arr::only($value, ['status']);
            });

            $calendarEvent->invitedAddresses()->sync($invitedAddresses);
        }

        if (array_key_exists('invited_users', $this->data)) {
            $invitedUsers = Arr::keyBy($this->data['invited_users'], 'id');
            $invitedUsers = Arr::map($invitedUsers, function ($value) {
                return Arr::only($value, ['status']);
            });

            $calendarEvent->invitedUsers()->sync($invitedUsers);
        }

        return $calendarEvent;
    }
}
