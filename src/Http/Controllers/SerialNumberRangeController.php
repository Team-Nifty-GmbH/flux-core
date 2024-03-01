<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Services\SerialNumberRangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SerialNumberRangeController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(SerialNumberRange::class);
    }

    public function create(Request $request, SerialNumberRangeService $serialNumberRangeService): JsonResponse
    {
        $serialNumberRange = $serialNumberRangeService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $serialNumberRange,
            statusMessage: 'serial number range created'
        );
    }

    public function update(Request $request, SerialNumberRangeService $serialNumberRangeService): JsonResponse
    {
        $response = $serialNumberRangeService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, SerialNumberRangeService $serialNumberRangeService): JsonResponse
    {
        $response = $serialNumberRangeService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
