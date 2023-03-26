<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateSerialNumberRangeRequest;
use FluxErp\Models\SerialNumberRange;
use FluxErp\Services\SerialNumberRangeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SerialNumberRangeController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new SerialNumberRange();
    }

    public function create(CreateSerialNumberRangeRequest $request,
        SerialNumberRangeService $serialNumberRangeService): JsonResponse
    {
        $serialNumberRange = $serialNumberRangeService->create($request->validated());

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
