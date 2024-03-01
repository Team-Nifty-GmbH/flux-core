<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use FluxErp\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(Permission::class);
    }

    public function showUserPermissions(string $id): JsonResponse
    {
        $user = app(User::class)->query()
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
            data: $user->permissions()->get()
        );
    }

    public function create(Request $request, PermissionService $permissionService): JsonResponse
    {
        $permission = $permissionService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $permission,
            statusMessage: 'permission created'
        );
    }

    public function give(Request $request, PermissionService $permissionService): JsonResponse
    {
        $permissions = $permissionService->editUserPermissions($request->all(), true);

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $permissions,
            statusMessage: 'user permissions updated'
        );
    }

    public function revoke(Request $request, PermissionService $permissionService): JsonResponse
    {
        $permissions = $permissionService->editUserPermissions($request->all(), false);

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $permissions,
            statusMessage: 'user permissions updated'
        );
    }

    public function sync(Request $request, PermissionService $permissionService): JsonResponse
    {
        $permissions = $permissionService->syncUserPermissions($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $permissions,
            statusMessage: 'user permissions updated'
        );
    }

    public function delete(string $id, PermissionService $permissionService): JsonResponse
    {
        $response = $permissionService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
