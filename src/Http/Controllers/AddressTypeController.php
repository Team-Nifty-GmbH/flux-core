<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateAddressRequest;
use FluxErp\Models\AddressType;
use FluxErp\Services\AddressTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressTypeController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new AddressType();
    }

    public function create(CreateAddressRequest $request, AddressTypeService $addressTypeService): JsonResponse
    {
        $addressType = $addressTypeService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $addressType,
            statusMessage: 'address type created'
        );
    }

    public function update(Request $request, AddressTypeService $addressTypeService): JsonResponse
    {
        $response = $addressTypeService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, AddressTypeService $addressTypeService): JsonResponse
    {
        $response = $addressTypeService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
