<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateAddressRequest;
use FluxErp\Models\Address;
use FluxErp\Services\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Address();
    }

    public function create(CreateAddressRequest $request, AddressService $addressService): JsonResponse
    {
        $address = $addressService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $address,
            statusMessage: 'address created'
        );
    }

    public function update(Request $request, AddressService $addressService): JsonResponse
    {
        $response = $addressService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, AddressService $addressService): JsonResponse
    {
        $response = $addressService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
