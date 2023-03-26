<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateOrderRequest;
use FluxErp\Models\Order;
use FluxErp\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Order();
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, OrderService $orderService): JsonResponse
    {
        $validator = Validator::make($request->all(), (new CreateOrderRequest())->rules());
        $validator->addModel($this->model);

        if ($validator->fails()) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 422,
                data: $validator->errors()->toArray()
            );
        }

        $order = $orderService->create($validator->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $order,
            statusMessage: 'order created'
        );
    }

    public function update(Request $request, OrderService $orderService): JsonResponse
    {
        $response = $orderService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, OrderService $orderService): JsonResponse
    {
        $response = $orderService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
