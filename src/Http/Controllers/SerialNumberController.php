<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\SerialNumber;
use FluxErp\Services\SerialNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SerialNumberController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(SerialNumber::class);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, SerialNumberService $serialNumberService): JsonResponse
    {
        $serialNumber = $serialNumberService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $serialNumber,
            statusMessage: 'serial number created'
        );
    }

    public function update(Request $request, SerialNumberService $serialNumberService): JsonResponse
    {
        $response = $serialNumberService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, SerialNumberService $serialNumberService): JsonResponse
    {
        $response = $serialNumberService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
