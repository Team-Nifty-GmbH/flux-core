<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateClientRequest;
use FluxErp\Models\Client;
use FluxErp\Services\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Client();
    }

    public function create(CreateClientRequest $request, ClientService $clientService): JsonResponse
    {
        $client = $clientService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $client,
            statusMessage: 'client created'
        );
    }

    public function update(Request $request, ClientService $clientService): JsonResponse
    {
        $response = $clientService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, ClientService $clientService): JsonResponse
    {
        $response = $clientService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
