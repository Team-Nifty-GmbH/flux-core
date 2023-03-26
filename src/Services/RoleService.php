<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateRoleRequest;
use FluxErp\Models\Role;
use Illuminate\Database\Eloquent\Model;

class RoleService
{
    public function create(array $data): Model
    {
        $role = Role::create($data);

        if ($data['permissions'] ?? false) {
            $role->givePermissionTo($data['permissions']);
        }

        return $role;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateRoleRequest(),
        );

        foreach ($data as $item) {
            $role = Role::query()
                ->whereKey($item['id'])
                ->first();

            $role->fill($item);
            $role->save();

            if ($item['permissions'] ?? false) {
                $role->syncPermissions($item['permissions']);
            }

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $role->withoutRelations()->fresh(),
                additions: ['id' => $role->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'roles updated',
            bulk: true
        );
    }

    public function editRolePermissions(array $data, bool $give): array
    {
        $role = Role::query()
            ->whereKey($data['id'])
            ->first();

        if ($give) {
            $role->givePermissionTo($data['permissions']);
        } else {
            foreach ($data['permissions'] as $permission) {
                $role->revokePermissionTo($permission);
            }
        }

        return $role->permissions->toArray();
    }

    public function editRoleUsers(array $data, bool $assign): array
    {
        $role = Role::query()
            ->whereKey($data['id'])
            ->first();

        if ($assign) {
            $role->users()->syncWithoutDetaching($data['users']);
        } else {
            $role->users()->detach($data['users']);
        }

        return $role->users->toArray();
    }

    public function delete(string $id): array
    {
        $role = Role::query()
            ->whereKey($id)
            ->first();

        if (! $role) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'role not found']
            );
        } elseif ($role->name === 'Super Admin') {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                statusMessage: __('cannot delete Super Admin role')
            );
        }

        $role->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: __('role deleted')
        );
    }
}
