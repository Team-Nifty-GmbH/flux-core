<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdatecalendarEventRequest;
use FluxErp\Models\CalendarEvent;
use Illuminate\Support\Arr;

class CalendarEventService
{
    public function create(array $data): CalendarEvent
    {
        $calendarEvent = new CalendarEvent($data);
        $calendarEvent->save();

        if (array_key_exists('invited_addresses', $data)) {
            $invitedAddresses = Arr::keyBy($data['invited_addresses'], 'id');
            $invitedAddresses = Arr::map($invitedAddresses, function ($value) {
                return Arr::only($value, ['status']);
            });

            $calendarEvent->invitedAddresses()->sync($invitedAddresses);
        }

        if (array_key_exists('invited_users', $data)) {
            $invitedUsers = Arr::keyBy($data['invited_users'], 'id');
            $invitedUsers = Arr::map($invitedUsers, function ($value) {
                return Arr::only($value, ['status']);
            });

            $calendarEvent->invitedUsers()->sync($invitedUsers);
        }

        return $calendarEvent;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateCalendarEventRequest()
        );

        foreach ($data as $item) {
            $calendarEvent = CalendarEvent::query()
                ->whereKey($item['id'])
                ->first();

            $calendarEvent->fill($item);
            $calendarEvent->save();

            if (array_key_exists('invited_addresses', $item)) {
                $invitedAddresses = Arr::keyBy($item['invited_addresses'], 'id');
                $invitedAddresses = Arr::map($invitedAddresses, function ($value) {
                    return Arr::only($value, ['status']);
                });

                $calendarEvent->invitedAddresses()->sync($invitedAddresses);
            }

            if (array_key_exists('invited_users', $item)) {
                $invitedUsers = Arr::keyBy($item['invited_users'], 'id');
                $invitedUsers = Arr::map($invitedUsers, function ($value) {
                    return Arr::only($value, ['status']);
                });

                $calendarEvent->invitedUsers()->sync($invitedUsers);
            }

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $calendarEvent->withoutRelations(),
                additions: ['id' => $calendarEvent->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'calendar event(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $calendarEvent = CalendarEvent::query()
            ->whereKey($id)
            ->first();

        if (! $calendarEvent) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'calendar event not found']
            );
        }

        $calendarEvent->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'calendar event deleted'
        );
    }
}
