<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateBankConnectionRequest;
use FluxErp\Models\BankConnection;
use FluxErp\Services\BankConnectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankConnectionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new BankConnection();
    }

    public function create(
        CreateBankConnectionRequest $request,
        BankConnectionService $bankConnectionService): JsonResponse
    {
        $bankConnection = $bankConnectionService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $bankConnection,
            statusMessage: 'contact bank connection created'
        );
    }

    public function update(Request $request, BankConnectionService $bankConnectionService): JsonResponse
    {
        $response = $bankConnectionService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, BankConnectionService $bankConnectionService): JsonResponse
    {
        $response = $bankConnectionService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
