<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateClientRequest;
use FluxErp\Models\Client;

class ClientService
{
    public function create(array $data): Client
    {
        $client = new Client($data);
        $client->save();

        return $client;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateClientRequest(),
            service: $this,
            model: new Client()
        );

        foreach ($data as $item) {
            $client = Client::query()
                ->whereKey($item['id'])
                ->first();

            $client->fill($item);
            $client->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $client->withoutRelations()->fresh(),
                additions: ['id' => $client->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'clients updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $client = Client::query()
            ->whereKey($id)
            ->first();

        if (! $client) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'client not found']
            );
        }

        $client->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'client deleted'
        );
    }
}
