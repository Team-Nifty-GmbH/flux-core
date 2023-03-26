<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateProductPropertyRequest;
use FluxErp\Models\ProductProperty;
use FluxErp\Services\ProductPropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductPropertyController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new ProductProperty();
    }

    public function create(CreateProductPropertyRequest $request,
        ProductPropertyService $productPropertyService): JsonResponse
    {
        $productProperty = $productPropertyService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $productProperty,
            statusMessage: 'product property created'
        );
    }

    public function update(Request $request, ProductPropertyService $productPropertyService): JsonResponse
    {
        $response = $productPropertyService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, ProductPropertyService $productPropertyService): JsonResponse
    {
        $response = $productPropertyService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
