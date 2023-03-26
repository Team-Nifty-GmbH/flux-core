<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateTransactionRequest;
use FluxErp\Models\Transaction;

class TransactionService
{
    public function create(array $data): Transaction
    {
        $transaction = new Transaction($data);
        $transaction->save();

        return $transaction;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateTransactionRequest(),
            service: $this
        );

        foreach ($data as $item) {
            $transaction = Transaction::query()
                ->whereKey($item['id'])
                ->first();

            $transaction->fill($item);
            $transaction->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $transaction->withoutRelations()->fresh(),
                additions: ['id' => $transaction->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'transaction(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $transaction = Transaction::query()
            ->whereKey($id)
            ->first();

        if (! $transaction) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'transaction not found']
            );
        }

        $transaction->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'transaction deleted'
        );
    }
}
