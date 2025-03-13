<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\User;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class RoleController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(Role::class);
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
}
