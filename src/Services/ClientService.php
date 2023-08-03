<?php

namespace FluxErp\Services;

use FluxErp\Actions\Client\CreateClient;
use FluxErp\Actions\Client\DeleteClient;
use FluxErp\Actions\Client\UpdateClient;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Client;
use Illuminate\Validation\ValidationException;

class ClientService
{
    public function create(array $data): Client
    {
        return CreateClient::make($data)->execute();
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
                    data: $client = UpdateClient::make($item)->validate()->execute(),
                    additions: ['id' => $client->id]
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
            statusMessage: $statusCode === 422 ? null : 'client(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        try {
            DeleteClient::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'client deleted'
        );
    }
}
