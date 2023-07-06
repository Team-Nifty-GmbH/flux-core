<?php

namespace FluxErp\Services;

use FluxErp\Actions\User\CreateUser;
use FluxErp\Actions\User\DeleteUser;
use FluxErp\Actions\User\UpdateUser;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Language;
use FluxErp\Models\User;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function create(array $data): User
    {
        return CreateUser::make($data)->execute();
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
                    data: $user = UpdateUser::make($item)->validate()->execute(),
                    additions: ['id' => $user->id]
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
            statusMessage: $statusCode === 422 ? null : 'user(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        try {
            DeleteUser::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 422,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'user deleted'
        );
    }
}
