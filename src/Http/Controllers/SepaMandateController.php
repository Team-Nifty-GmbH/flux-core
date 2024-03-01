<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\SepaMandate;
use FluxErp\Services\SepaMandateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SepaMandateController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(SepaMandate::class);
    }

    public function create(Request $request, SepaMandateService $sepaMandateService): JsonResponse
    {
        $response = $sepaMandateService->create($request->all());

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
