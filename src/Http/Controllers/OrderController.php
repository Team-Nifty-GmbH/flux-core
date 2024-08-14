<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\Order\ToggleLock;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Order;
use FluxErp\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(Order::class);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, OrderService $orderService): JsonResponse
    {
        $order = $orderService->create($request->all());

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

    public function toggleLock(string $id): JsonResponse
    {
       $order = ToggleLock::make(['id' => $id])
            ->validate()
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $order,
            statusMessage: 'order locked toggled'
        );
    }
}
