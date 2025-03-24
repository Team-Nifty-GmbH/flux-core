<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\User;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    public function showUserPermissions(string $id): JsonResponse
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
            data: $user->permissions()->get()
        );
    }
}
