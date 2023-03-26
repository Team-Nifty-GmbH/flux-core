<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateProductRequest;
use FluxErp\Models\Product;
use FluxErp\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Product();
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, ProductService $productService): JsonResponse
    {
        $validator = Validator::make($request->all(), (new CreateProductRequest())->rules());
        $validator->addModel($this->model);

        if ($validator->fails()) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 422,
                data: $validator->errors()->toArray()
            );
        }

        $product = $productService->create($validator->validated());

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
