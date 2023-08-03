<?php

namespace FluxErp\Services;

use FluxErp\Actions\Account\CreateAccount;
use FluxErp\Actions\Account\DeleteAccount;
use FluxErp\Actions\Account\UpdateAccount;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Account;
use Illuminate\Validation\ValidationException;

class AccountService
{
    public function create(array $data): Account
    {
        return CreateAccount::make($data)->execute();
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
                    data: $account = UpdateAccount::make($item)->validate()->execute(),
                    additions: ['id' => $account->id]
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
            statusMessage: $statusCode === 422 ? null : 'account(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        try {
            DeleteAccount::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'account deleted'
        );
    }
}
