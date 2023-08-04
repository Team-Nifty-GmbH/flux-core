<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreatePermissionRequest;
use FluxErp\Http\Requests\EditUserPermissionRequest;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use FluxErp\Services\PermissionService;
use Illuminate\Http\JsonResponse;

class PermissionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Permission();
    }

    public function showUserPermissions(string $id): JsonResponse
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
            data: $user->permissions()->get()
        );
    }

    public function create(CreatePermissionRequest $request, PermissionService $permissionService): JsonResponse
    {
        $permission = $permissionService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $permission,
            statusMessage: 'permission created'
        );
    }

    public function give(EditUserPermissionRequest $request, PermissionService $permissionService): JsonResponse
    {
        $permissions = $permissionService->editUserPermissions($request->validated(), true);

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $permissions,
            statusMessage: 'user permissions updated'
        );
    }

    public function revoke(EditUserPermissionRequest $request, PermissionService $permissionService): JsonResponse
    {
        $permissions = $permissionService->editUserPermissions($request->validated(), false);

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $permissions,
            statusMessage: 'user permissions updated'
        );
    }

    public function sync(EditUserPermissionRequest $request, PermissionService $permissionService): JsonResponse
    {
        $permissions = $permissionService->syncUserPermissions($request->validated());

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
