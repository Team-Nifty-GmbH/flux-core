<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateProductOptionRequest;
use FluxErp\Models\ProductOption;
use FluxErp\Services\ProductOptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductOptionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new ProductOption();
    }

    public function create(CreateProductOptionRequest $request,
        ProductOptionService $productOptionService): JsonResponse
    {
        $productOption = $productOptionService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $productOption,
            statusMessage: 'product option created'
        );
    }

    public function update(Request $request, ProductOptionService $productOptionService): JsonResponse
    {
        $response = $productOptionService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, ProductOptionService $productOptionService): JsonResponse
    {
        $response = $productOptionService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
