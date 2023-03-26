<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateDiscountRequest;
use FluxErp\Models\Discount;
use FluxErp\Services\DiscountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscountController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Discount();
    }

    public function create(CreateDiscountRequest $request, DiscountService $discountService): JsonResponse
    {
        $discount = $discountService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $discount,
            statusMessage: 'discount created'
        );
    }

    public function update(Request $request, DiscountService $discountService): JsonResponse
    {
        $response = $discountService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, DiscountService $discountService): JsonResponse
    {
        $response = $discountService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
