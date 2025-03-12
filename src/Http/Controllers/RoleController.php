<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\User;
use FluxErp\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(Role::class);
    }

    public function assignUsers(Request $request, RoleService $roleService): JsonResponse
    {
        $users = $roleService->editRoleUsers($request->all(), true);

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $users,
            statusMessage: 'role users updated'
        );
    }

    public function create(Request $request, RoleService $roleService): JsonResponse
    {
        $role = $roleService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $role,
            statusMessage: __('role created')
        );
    }

    public function delete(string $id, RoleService $roleService): JsonResponse
    {
        $response = $roleService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function give(Request $request, RoleService $roleService): JsonResponse
    {
        $permissions = $roleService->editRolePermissions($request->all(), true);

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $permissions,
            statusMessage: 'role permissions updated'
        );
    }

    public function revoke(Request $request, RoleService $roleService): JsonResponse
    {
        $permissions = $roleService->editRolePermissions($request->all(), false);

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $permissions,
            statusMessage: 'role permissions updated'
        );
    }

    public function revokeUsers(Request $request, RoleService $roleService): JsonResponse
    {
        $users = $roleService->editRoleUsers($request->all(), false);

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $users,
            statusMessage: 'role users updated'
        );
    }

    public function showUserRoles(string $id): JsonResponse
    {
        $user = resolve_static(User::class, 'query')
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

    public function syncUserRoles(Request $request, RoleService $roleService): JsonResponse
    {
        $roles = $roleService->syncUserRoles($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $roles,
            statusMessage: 'user roles updated'
        );
    }

    public function update(Request $request, RoleService $roleService): JsonResponse
    {
        $response = $roleService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
