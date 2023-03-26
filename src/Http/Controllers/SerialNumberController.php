<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateSerialNumberRequest;
use FluxErp\Models\SerialNumber;
use FluxErp\Services\SerialNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SerialNumberController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new SerialNumber();
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(CreateSerialNumberRequest $request, SerialNumberService $serialNumberService): JsonResponse
    {
        $validator = Validator::make($request->all(), (new CreateSerialNumberRequest())->rules());
        $validator->addModel($this->model);

        if ($validator->fails()) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 422,
                data: $validator->errors()->toArray()
            );
        }

        $serialNumber = $serialNumberService->create($validator->validated());

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
