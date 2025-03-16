<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Warehouse;
use FluxErp\Services\WarehouseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(Warehouse::class);
    }

    public function create(Request $request, WarehouseService $warehouseService): JsonResponse
    {
        $warehouse = $warehouseService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $warehouse,
            statusMessage: 'warehouse created'
        );
    }

    public function delete(string $id, WarehouseService $warehouseService): JsonResponse
    {
        $response = $warehouseService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(Request $request, WarehouseService $warehouseService): JsonResponse
    {
        $response = $warehouseService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
