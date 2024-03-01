<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Unit;
use FluxErp\Services\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(Unit::class);
    }

    public function create(Request $request, UnitService $unitService): JsonResponse
    {
        $unit = $unitService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $unit,
            statusMessage: 'unit created'
        );
    }

    public function delete(string $id, UnitService $unitService): JsonResponse
    {
        $response = $unitService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
