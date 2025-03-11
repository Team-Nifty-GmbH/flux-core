<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Services\ContactBankConnectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactBankConnectionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(ContactBankConnection::class);
    }

    public function create(Request $request, ContactBankConnectionService $bankConnectionService): JsonResponse
    {
        $contactBankConnection = $bankConnectionService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $contactBankConnection,
            statusMessage: 'contact bank connection created'
        );
    }

    public function delete(string $id, ContactBankConnectionService $contactBankConnectionService): JsonResponse
    {
        $response = $contactBankConnectionService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(Request $request, ContactBankConnectionService $contactBankConnectionService): JsonResponse
    {
        $response = $contactBankConnectionService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
