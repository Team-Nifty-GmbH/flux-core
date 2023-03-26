<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreatePriceListRequest;
use FluxErp\Models\PriceList;
use FluxErp\Services\PriceListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PriceListController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new PriceList();
    }

    public function create(CreatePriceListRequest $request, PriceListService $priceListService): JsonResponse
    {
        $priceList = $priceListService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $priceList,
            statusMessage: 'price-list created'
        );
    }

    public function update(Request $request, PriceListService $priceListService): JsonResponse
    {
        $response = $priceListService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, PriceListService $priceListService): JsonResponse
    {
        $response = $priceListService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
