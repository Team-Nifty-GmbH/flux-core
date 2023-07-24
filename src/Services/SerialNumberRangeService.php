<?php

namespace FluxErp\Services;

use FluxErp\Actions\SerialNumberRange\CreateSerialNumberRange;
use FluxErp\Actions\SerialNumberRange\DeleteSerialNumberRange;
use FluxErp\Actions\SerialNumberRange\UpdateSerialNumberRange;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\SerialNumberRange;
use Illuminate\Validation\ValidationException;

class SerialNumberRangeService
{
    public function create(array $data): SerialNumberRange
    {
        return CreateSerialNumberRange::make($data)->execute();
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
                    data: $serialNumberRange = UpdateSerialNumberRange::make($item)->validate()->execute(),
                    additions: ['id' => $serialNumberRange->id]
                );
            } catch (ValidationException $e) {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: array_key_exists('serial_numbers', $e->errors()) ? 423 : 422,
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
            statusMessage: $statusCode === 422 ? null : 'serial number range(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        try {
            DeleteSerialNumberRange::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'serial number range deleted'
        );
    }
}
