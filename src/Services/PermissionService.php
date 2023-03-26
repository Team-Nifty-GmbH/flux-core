<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Model;

class PermissionService
{
    public function create(array $data): Model
    {
        return Permission::create($data);
    }

    public function editUserPermissions(array $data, bool $give): array
    {
        $user = User::query()
            ->whereKey($data['user_id'])
            ->first();

        if ($give) {
            $user->givePermissionTo($data['permissions']);
        } else {
            foreach ($data['permissions'] as $permission) {
                $user->revokePermissionTo($permission);
            }
        }

        return $user->permissions->toArray();
    }

    public function delete(string $id): array
    {
        $permission = Permission::query()
            ->whereKey($id)
            ->first();

        if (! $permission) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'permission not found']
            );
        } elseif ($permission->is_locked) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                statusMessage: __('permission is locked')
            );
        }

        $permission->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: __('permission deleted')
        );
    }
}
