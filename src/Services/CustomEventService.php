<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateCustomEventRequest;
use FluxErp\Models\CustomEvent;

class CustomEventService
{
    public function create(array $data): CustomEvent
    {
        $customEvent = new CustomEvent($data);
        $customEvent->save();

        return $customEvent;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateCustomEventRequest(),
        );

        foreach ($data as $item) {
            $customEvent = CustomEvent::query()
                ->whereKey($item['id'])
                ->first();

            // Save new data to table.
            $customEvent->fill($item);
            $customEvent->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $customEvent->withoutRelations()->fresh(),
                additions: ['id' => $customEvent->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'custom event(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $customEvent = CustomEvent::query()
            ->whereKey($id)
            ->first();

        if (! $customEvent) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'custom event not found']
            );
        }

        $customEvent->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'custom event deleted'
        );
    }
}
