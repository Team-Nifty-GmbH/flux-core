<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Services\AdditionalColumnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdditionalColumnController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(AdditionalColumn::class);
    }

    public function create(Request $request, AdditionalColumnService $additionalColumnService): JsonResponse
    {
        $response = $additionalColumnService->create($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(Request $request, AdditionalColumnService $additionalColumnService): JsonResponse
    {
        $response = $additionalColumnService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, AdditionalColumnService $additionalColumnService): JsonResponse
    {
        $response = $additionalColumnService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
