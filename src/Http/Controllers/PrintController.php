<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\Printing;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\GetPrintViewsRequest;
use FluxErp\Http\Requests\PrintingRequest;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PrintController extends Controller
{
    public function getPrintViews(GetPrintViewsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $modelType = $validated['model_type'];
        if ($validated['model_id'] ?? false) {
            $views = array_keys(
                $modelType::query()
                    ->whereKey($validated['model_id'])
                    ->first()
                    ->resolvePrintViews()
            );
        } else {
            $views = (new $modelType())->getAvailableViews();
        }

        return ResponseHelper::createResponseFromBase(statusCode: 200, data: $views)
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function render(PrintingRequest $request): View|Factory|Response
    {
        $data = $request->validated();
        $data['html'] = true;
        $data['preview'] = false;

        return Printing::make($data)->execute();
    }

    public function renderPdf(PrintingRequest $request): Response
    {
        $data = $request->validated();
        $data['html'] = false;

        return Printing::make($data)->execute()->streamPDF();
    }
}
