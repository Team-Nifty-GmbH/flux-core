<?php

namespace FluxErp\Services;

use FluxErp\Actions\Permission\CreatePermission;
use FluxErp\Actions\Permission\DeletePermission;
use FluxErp\Actions\Permission\UpdateUserPermissions;
use FluxErp\Helpers\ResponseHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class PermissionService
{
    public function create(array $data): Model
    {
        return CreatePermission::make($data)->validate()->execute();
    }

    public function delete(string $id): array
    {
        try {
            DeletePermission::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: array_key_exists('id', $e->errors()) ? 404 : 423,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: __('permission deleted')
        );
    }

    public function editUserPermissions(array $data, bool $give): array
    {
        return UpdateUserPermissions::make(array_merge($data, ['give' => $give]))->validate()->execute();
    }

    public function syncUserPermissions(array $data): array
    {
        return UpdateUserPermissions::make(array_merge($data, ['sync' => true]))->validate()->execute();
    }
}
