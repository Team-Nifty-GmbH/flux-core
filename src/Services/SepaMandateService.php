<?php

namespace FluxErp\Services;

use FluxErp\Actions\SepaMandate\CreateSepaMandate;
use FluxErp\Actions\SepaMandate\DeleteSepaMandate;
use FluxErp\Actions\SepaMandate\UpdateSepaMandate;
use FluxErp\Helpers\ResponseHelper;
use Illuminate\Validation\ValidationException;

class SepaMandateService
{
    public function create(array $data): array
    {
        try {
            return ResponseHelper::createArrayResponse(
                statusCode: 201,
                data: CreateSepaMandate::make($data)->validate()->execute()
            );
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(statusCode: 422, data: $e->errors());
        }
    }

    public function delete(string $id): array
    {
        try {
            DeleteSepaMandate::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'sepa mandate deleted'
        );
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
                    data: $sepaMandate = UpdateSepaMandate::make($item)->validate()->execute(),
                    additions: ['id' => $sepaMandate->id]
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
            statusMessage: $statusCode === 422 ? null : 'sepa mandates updated',
            bulk: true
        );
    }
}
