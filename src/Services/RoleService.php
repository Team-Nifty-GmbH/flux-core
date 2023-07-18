<?php

namespace FluxErp\Services;

use FluxErp\Actions\Role\CreateRole;
use FluxErp\Actions\Role\DeleteRole;
use FluxErp\Actions\Role\UpdateRole;
use FluxErp\Actions\Role\UpdateRolePermissions;
use FluxErp\Actions\Role\UpdateRoleUsers;
use FluxErp\Helpers\ResponseHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class RoleService
{
    public function create(array $data): Model
    {
        return CreateRole::make($data)->execute();
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
                    data: $role = UpdateRole::make($item)->validate()->execute(),
                    additions: ['id' => $role->id]
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
            statusMessage: $statusCode === 422 ? null : 'role(s) updated',
            bulk: true
        );
    }

    public function editRolePermissions(array $data, bool $give): array
    {
        return UpdateRolePermissions::make(array_merge($data, ['give' => $give]))->execute();
    }

    public function editRoleUsers(array $data, bool $assign): array
    {
        return UpdateRoleUsers::make(array_merge($data, ['assign' => $assign]))->execute();
    }

    public function delete(string $id): array
    {
        try {
            DeleteRole::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: array_key_exists('id', $e->errors()) ? 404 : 423,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'role deleted'
        );
    }
}
