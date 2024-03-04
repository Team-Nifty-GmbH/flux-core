<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\Printing;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\GetPrintViewsRequest;
use FluxErp\Http\Requests\PrintingRequest;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PrintController extends Controller
{
    public function getPrintViews(GetPrintViewsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $modelType = Relation::getMorphedModel($validated['model_type']);
        if ($validated['model_id'] ?? false) {
            $views = array_keys(
                app($modelType)->query()
                    ->whereKey($validated['model_id'])
                    ->first()
                    ->resolvePrintViews()
            );
        } else {
            $views = app($modelType)->getAvailableViews();
        }

        return ResponseHelper::createResponseFromBase(statusCode: 200, data: $views)
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function render(Request $request): View|Factory|Response
    {
        $data = $request->all();
        $data['html'] = true;
        $data['preview'] = false;

        return Printing::make($data)
            ->validate()
            ->execute();
    }

    public function renderPdf(Request $request): Response
    {
        $data = $request->all();
        $data['html'] = false;

        return Printing::make($data)
            ->validate()
            ->execute()
            ->streamPDF();
    }
}
