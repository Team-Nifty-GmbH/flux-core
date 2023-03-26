<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateAdditionalColumnRequest;
use FluxErp\Http\Requests\UpdateAdditionalColumnRequest;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Services\AdditionalColumnService;
use Illuminate\Http\JsonResponse;

class AdditionalColumnController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new AdditionalColumn();
    }

    public function create(
        CreateAdditionalColumnRequest $request,
        AdditionalColumnService $additionalColumnService): JsonResponse
    {
        $response = $additionalColumnService->create($request->validated());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(
        UpdateAdditionalColumnRequest $request,
        AdditionalColumnService $additionalColumnService): JsonResponse
    {
        $response = $additionalColumnService->update($request->validated());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, AdditionalColumnService $additionalColumnService): JsonResponse
    {
        $response = $additionalColumnService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
