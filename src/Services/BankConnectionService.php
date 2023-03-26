<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateBankConnectionRequest;
use FluxErp\Models\BankConnection;

class BankConnectionService
{
    public function create(array $data): BankConnection
    {
        $bankConnection = new BankConnection($data);
        $bankConnection->save();

        return $bankConnection;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateBankConnectionRequest()
        );

        foreach ($data as $item) {
            $bankConnection = BankConnection::query()
                ->whereKey($item['id'])
                ->first();

            $bankConnection->fill($item);
            $bankConnection->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $bankConnection->withoutRelations()->fresh(),
                additions: ['id' => $bankConnection->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'contact bank connections updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $bankConnection = BankConnection::query()
            ->whereKey($id)
            ->first();

        if (! $bankConnection) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'contact bank connection not found']
            );
        }

        $bankConnection->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'contact bank connection deleted'
        );
    }
}
