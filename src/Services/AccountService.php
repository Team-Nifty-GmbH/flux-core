<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateAccountRequest;
use FluxErp\Models\Account;

class AccountService
{
    public function create(array $data): Account
    {
        $account = new Account($data);
        $account->save();

        return $account;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateAccountRequest(),
            service: $this
        );

        foreach ($data as $item) {
            $account = Account::query()
                ->whereKey($item['id'])
                ->first();

            $account->fill($item);
            $account->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $account->withoutRelations()->fresh(),
                additions: ['id' => $account->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'account(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $account = Account::query()
            ->whereKey($id)
            ->first();

        if (! $account) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'account not found']
            );
        }

        $account->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'account deleted'
        );
    }
}
