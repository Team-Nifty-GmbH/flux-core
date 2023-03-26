<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreatePaymentTypeRequest;
use FluxErp\Models\PaymentType;
use FluxErp\Services\PaymentTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentTypeController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new PaymentType();
    }

    public function create(CreatePaymentTypeRequest $request, PaymentTypeService $paymentTypeService): JsonResponse
    {
        $paymentType = $paymentTypeService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $paymentType,
            statusMessage: 'payment type created'
        );
    }

    public function update(Request $request, PaymentTypeService $paymentTypeService): JsonResponse
    {
        $response = $paymentTypeService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, PaymentTypeService $paymentTypeService): JsonResponse
    {
        $response = $paymentTypeService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
