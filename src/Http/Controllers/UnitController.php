<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateUnitRequest;
use FluxErp\Models\Unit;
use FluxErp\Services\UnitService;
use Illuminate\Http\JsonResponse;

class UnitController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Unit();
    }

    public function create(CreateUnitRequest $request, UnitService $unitService): JsonResponse
    {
        $unit = $unitService->create($request->validated());

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
