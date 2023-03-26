<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateWarehouseRequest;
use FluxErp\Models\Warehouse;
use FluxErp\Services\WarehouseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Warehouse();
    }

    public function create(CreateWarehouseRequest $request, WarehouseService $warehouseService): JsonResponse
    {
        $warehouse = $warehouseService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $warehouse,
            statusMessage: 'warehouse created'
        );
    }

    public function update(Request $request, WarehouseService $warehouseService): JsonResponse
    {
        $response = $warehouseService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, WarehouseService $warehouseService): JsonResponse
    {
        $response = $warehouseService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
