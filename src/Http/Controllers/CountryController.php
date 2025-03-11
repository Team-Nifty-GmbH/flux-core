<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Country;
use FluxErp\Services\CountryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(Country::class);
    }

    public function create(Request $request, CountryService $countryService): JsonResponse
    {
        $country = $countryService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $country,
            statusMessage: 'country created'
        );
    }

    public function delete(string $id, CountryService $countryService): JsonResponse
    {
        $response = $countryService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(Request $request, CountryService $countryService): JsonResponse
    {
        $response = $countryService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
