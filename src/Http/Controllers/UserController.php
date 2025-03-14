<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\User;
use FluxErp\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(User::class);
    }

    public function create(Request $request, UserService $userService): JsonResponse
    {
        $user = $userService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $user,
            statusMessage: 'user created'
        );
    }

    public function delete(string $id, UserService $userService): JsonResponse
    {
        $response = $userService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(Request $request, UserService $userService): JsonResponse
    {
        $response = $userService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
