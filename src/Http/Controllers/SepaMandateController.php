<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateSepaMandateRequest;
use FluxErp\Models\SepaMandate;
use FluxErp\Services\SepaMandateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SepaMandateController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new SepaMandate();
    }

    public function create(CreateSepaMandateRequest $request, SepaMandateService $sepaMandateService): JsonResponse
    {
        $response = $sepaMandateService->create($request->validated());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(Request $request, SepaMandateService $sepaMandateService): JsonResponse
    {
        $response = $sepaMandateService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, SepaMandateService $sepaMandateService): JsonResponse
    {
        $response = $sepaMandateService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
