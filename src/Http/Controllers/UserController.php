<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateUserRequest;
use FluxErp\Models\User;
use FluxErp\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new User();
    }

    public function create(CreateUserRequest $request, UserService $userService): JsonResponse
    {
        $user = $userService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $user,
            statusMessage: 'user created'
        );
    }

    public function update(Request $request, UserService $userService): JsonResponse
    {
        $response = $userService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, UserService $userService): JsonResponse
    {
        $response = $userService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
