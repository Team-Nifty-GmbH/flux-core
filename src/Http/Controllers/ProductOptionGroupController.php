<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateProductOptionGroupRequest;
use FluxErp\Models\ProductOptionGroup;
use FluxErp\Services\ProductOptionGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductOptionGroupController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new ProductOptionGroup();
    }

    public function create(CreateProductOptionGroupRequest $request,
        ProductOptionGroupService $productOptionGroupService): JsonResponse
    {
        $productOptionGroup = $productOptionGroupService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $productOptionGroup,
            statusMessage: 'product option group created'
        );
    }

    public function update(Request $request, ProductOptionGroupService $productOptionGroupService): JsonResponse
    {
        $response = $productOptionGroupService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, ProductOptionGroupService $productOptionGroupService): JsonResponse
    {
        $response = $productOptionGroupService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
