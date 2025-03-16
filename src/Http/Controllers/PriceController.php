<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Price;
use FluxErp\Services\PriceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(Price::class);
    }

    public function create(Request $request, PriceService $priceService): JsonResponse
    {
        $price = $priceService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $price,
            statusMessage: 'price created'
        );
    }

    public function delete(string $id, PriceService $priceService): JsonResponse
    {
        $response = $priceService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(Request $request, PriceService $priceService): JsonResponse
    {
        $response = $priceService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
