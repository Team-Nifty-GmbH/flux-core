<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\ContactOption;
use FluxErp\Services\ContactOptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactOptionController extends Controller
{
    public function index(string $addressId): JsonResponse
    {
        $contactOptions = resolve_static(ContactOption::class, 'query')
            ->where('address_id', $addressId)
            ->orderBy('type', 'ASC')
            ->orderBy('label', 'ASC')
            ->get();

        return ResponseHelper::createResponseFromBase(statusCode: 200, data: $contactOptions);
    }

    public function create(Request $request, ContactOptionService $contactOptionService): JsonResponse
    {
        $contactOption = $contactOptionService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $contactOption,
            statusMessage: 'contact option created'
        );
    }

    public function update(Request $request, ContactOptionService $contactOptionService): JsonResponse
    {
        $response = $contactOptionService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, ContactOptionService $contactOptionService): JsonResponse
    {
        $response = $contactOptionService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
