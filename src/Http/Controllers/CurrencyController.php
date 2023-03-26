<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateCurrencyRequest;
use FluxErp\Models\Currency;
use FluxErp\Services\CurrencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Currency();
    }

    public function create(CreateCurrencyRequest $request, CurrencyService $currencyService): JsonResponse
    {
        $currency = $currencyService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $currency,
            statusMessage: 'currency created'
        );
    }

    public function update(Request $request, CurrencyService $currencyService): JsonResponse
    {
        $response = $currencyService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, CurrencyService $currencyService): JsonResponse
    {
        $response = $currencyService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
