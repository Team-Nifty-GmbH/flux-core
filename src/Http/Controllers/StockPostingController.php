<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateStockPostingRequest;
use FluxErp\Models\StockPosting;
use FluxErp\Services\StockPostingService;
use Illuminate\Http\JsonResponse;

class StockPostingController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new StockPosting();
    }

    public function create(CreateStockPostingRequest $request, StockPostingService $stockPostingService): JsonResponse
    {
        $stockPosting = $stockPostingService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $stockPosting,
            statusMessage: 'stock posting created'
        );
    }

    public function delete(string $id, StockPostingService $stockPostingService): JsonResponse
    {
        $response = $stockPostingService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
