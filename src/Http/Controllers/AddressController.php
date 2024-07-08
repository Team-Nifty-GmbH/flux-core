<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Address;
use FluxErp\Services\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(Address::class);
    }

    public function create(Request $request, AddressService $addressService): JsonResponse
    {
        $address = $addressService->create($request->all());

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

    public function generateLoginToken(Address $address): JsonResponse
    {
        $token = $address->createLoginToken();

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $token,
            statusMessage: 'login token generated'
        );
    }
}
