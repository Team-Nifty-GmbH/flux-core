<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Product;
use FluxErp\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(Product::class);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, ProductService $productService): JsonResponse
    {
        $product = $productService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $product,
            statusMessage: 'product created'
        );
    }

    public function update(Request $request, ProductService $productService): JsonResponse
    {
        $response = $productService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, ProductService $productService): JsonResponse
    {
        $response = $productService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
