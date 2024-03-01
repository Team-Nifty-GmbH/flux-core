<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\VatRate;
use FluxErp\Services\VatRateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VatRateController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(VatRate::class);
    }

    public function create(Request $request, VatRateService $vatRateService): JsonResponse
    {
        $vatRate = $vatRateService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $vatRate,
            statusMessage: 'vat rate created'
        );
    }

    public function update(Request $request, VatRateService $vatRateService): JsonResponse
    {
        $response = $vatRateService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, VatRateService $vatRateService): JsonResponse
    {
        $response = $vatRateService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
