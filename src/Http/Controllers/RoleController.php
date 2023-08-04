<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateRoleRequest;
use FluxErp\Http\Requests\EditRolePermissionRequest;
use FluxErp\Http\Requests\EditRoleUserRequest;
use FluxErp\Http\Requests\EditUserRoleRequest;
use FluxErp\Http\Requests\UpdateRoleRequest;
use FluxErp\Models\User;
use FluxErp\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class RoleController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Role();
    }

    public function showUserRoles(string $id): JsonResponse
    {
        $user = User::query()
            ->whereKey($id)
            ->first();

        if (! $user) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 404,
                data: ['id' => 'user not found']
            );
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $user->roles
        );
    }

    public function create(CreateRoleRequest $request, RoleService $roleService): JsonResponse
    {
        $role = $roleService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $role,
            statusMessage: __('role created')
        );
    }

    public function update(UpdateRoleRequest $request, RoleService $roleService): JsonResponse
    {
        $response = $roleService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function give(EditRolePermissionRequest $request, RoleService $roleService): JsonResponse
    {
        $permissions = $roleService->editRolePermissions($request->validated(), true);

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $permissions,
            statusMessage: 'role permissions updated'
        );
    }

    public function revoke(EditRolePermissionRequest $request, RoleService $roleService): JsonResponse
    {
        $permissions = $roleService->editRolePermissions($request->validated(), false);

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $permissions,
            statusMessage: 'role permissions updated'
        );
    }

    public function syncUsers(EditUserRoleRequest $request, RoleService $roleService): JsonResponse
    {
        $roles = $roleService->syncUserRoles($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $roles,
            statusMessage: 'role permissions updated'
        );
    }

    public function assignUsers(EditRoleUserRequest $request, RoleService $roleService): JsonResponse
    {
        $users = $roleService->editRoleUsers($request->validated(), true);

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $users,
            statusMessage: 'role users updated'
        );
    }

    public function revokeUsers(EditRoleUserRequest $request, RoleService $roleService): JsonResponse
    {
        $users = $roleService->editRoleUsers($request->validated(), false);

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $users,
            statusMessage: 'role users updated'
        );
    }

    public function delete(string $id, RoleService $roleService): JsonResponse
    {
        $response = $roleService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
