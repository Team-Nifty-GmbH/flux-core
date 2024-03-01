<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\StockPosting;
use FluxErp\Services\StockPostingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockPostingController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(StockPosting::class);
    }

    public function create(Request $request, StockPostingService $stockPostingService): JsonResponse
    {
        $stockPosting = $stockPostingService->create($request->all());

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
