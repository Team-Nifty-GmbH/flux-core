<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Client;
use FluxErp\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(Client::class);
    }

    public function create(Request $request, ClientService $clientService): JsonResponse
    {
        $client = $clientService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $client,
            statusMessage: 'client created'
        );
    }

    public function delete(string $id, ClientService $clientService): JsonResponse
    {
        $response = $clientService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(Request $request, ClientService $clientService): JsonResponse
    {
        $response = $clientService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
