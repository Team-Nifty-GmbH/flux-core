<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Transaction;
use FluxErp\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(Transaction::class);
    }

    public function create(Request $request, TransactionService $transactionService): JsonResponse
    {
        $transaction = $transactionService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $transaction,
            statusMessage: 'transaction created'
        );
    }

    public function delete(string $id, TransactionService $transactionService): JsonResponse
    {
        $response = $transactionService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(Request $request, TransactionService $transactionService): JsonResponse
    {
        $response = $transactionService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
