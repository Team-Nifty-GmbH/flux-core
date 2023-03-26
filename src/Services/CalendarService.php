<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateCalendarRequest;
use FluxErp\Models\Calendar;

class CalendarService
{
    public function create(array $data): Calendar
    {
        $calendar = new Calendar($data);
        $calendar->save();

        return $calendar;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateCalendarRequest()
        );

        foreach ($data as $item) {
            $calendar = Calendar::query()
                ->whereKey($item['id'])
                ->first();

            $calendar->fill($item);
            $calendar->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $calendar->withoutRelations(),
                additions: ['id' => $calendar->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'calendar(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $calendar = Calendar::query()
            ->whereKey($id)
            ->first();

        if (! $calendar) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'calendar not found']
            );
        }

        $calendar->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'calendar deleted'
        );
    }
}
