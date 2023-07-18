<?php

namespace FluxErp\Services;

use FluxErp\Actions\CustomEvent\CreateCustomEvent;
use FluxErp\Actions\CustomEvent\DeleteCustomEvent;
use FluxErp\Actions\CustomEvent\UpdateCustomEvent;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\CustomEvent;
use Illuminate\Validation\ValidationException;

class CustomEventService
{
    public function create(array $data): CustomEvent
    {
        return CreateCustomEvent::make($data)->execute();
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = [];
        foreach ($data as $key => $item) {
            try {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 200,
                    data: $customEvent = UpdateCustomEvent::make($item)->validate()->execute(),
                    additions: ['id' => $customEvent->id]
                );
            } catch (ValidationException $e) {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 422,
                    data: $e->errors(),
                    additions: [
                        'id' => array_key_exists('id', $item) ? $item['id'] : null,
                    ]
                );

                unset($data[$key]);
            }
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
        try {
            DeleteCustomEvent::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'custom event deleted'
        );
    }
}
