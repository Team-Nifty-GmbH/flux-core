<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateAccountRequest;
use FluxErp\Models\Account;
use FluxErp\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Account();
    }

    public function create(CreateAccountRequest $request, AccountService $accountService): JsonResponse
    {
        $account = $accountService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $account,
            statusMessage: 'account created'
        );
    }

    public function update(Request $request, AccountService $accountService): JsonResponse
    {
        $response = $accountService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, AccountService $accountService): JsonResponse
    {
        $response = $accountService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
