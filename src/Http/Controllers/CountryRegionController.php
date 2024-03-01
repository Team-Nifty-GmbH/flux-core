<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\CountryRegion;
use FluxErp\Services\CountryRegionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryRegionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(CountryRegion::class);
    }

    public function create(Request $request, CountryRegionService $countryRegionService): JsonResponse
    {
        $countryRegion = $countryRegionService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $countryRegion,
            statusMessage: 'country region created'
        );
    }

    public function update(Request $request, CountryRegionService $countryRegionService): JsonResponse
    {
        $response = $countryRegionService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, CountryRegionService $countryRegionService): JsonResponse
    {
        $response = $countryRegionService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
