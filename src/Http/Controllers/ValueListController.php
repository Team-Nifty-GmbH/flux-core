<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Services\ValueListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValueListController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(AdditionalColumn::class);
    }

    public function create(Request $request, ValueListService $valueListService): JsonResponse
    {
        $response = $valueListService->create($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, ValueListService $valueListService): JsonResponse
    {
        $response = $valueListService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(Request $request, ValueListService $valueListService): JsonResponse
    {
        $response = $valueListService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
