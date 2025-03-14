<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\GetPrintViewsRequest;
use Illuminate\Http\JsonResponse;

class PrintController extends Controller
{
    public function getPrintViews(GetPrintViewsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $modelType = morphed_model(data_get($validated, 'model_type'));
        if (data_get($validated, 'model_id')) {
            $views = array_keys(
                resolve_static($modelType, 'query')
                    ->whereKey(data_get($validated, 'model_id'))
                    ->first()
                    ->resolvePrintViews()
            );
        } else {
            $views = app($modelType)->getAvailableViews();
        }

        return ResponseHelper::createResponseFromBase(statusCode: 200, data: $views)
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }
}
