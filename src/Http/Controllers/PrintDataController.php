<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\GeneratePdfFromViewRequest;
use FluxErp\Http\Requests\UpdatePrintRequest;
use FluxErp\Models\PrintData;
use FluxErp\Services\PrintDataService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PrintDataController extends BaseController
{
    private PrintDataService $printService;

    public function __construct()
    {
        parent::__construct();
        $this->model = new PrintData();
        $this->printService = new PrintDataService();
    }

    public function showHtmlPublic(Request $request, string $uuid): JsonResponse|View
    {
        $printData = PrintData::query()
            ->where('uuid', $uuid)
            ->where('is_public', true)
            ->first();

        if (! $printData) {
            abort(404);
        }
        $response = $this->printService->showHtml($request->all(), $printData->id);

        return is_array($response) ? ResponseHelper::createResponseFromArrayResponse($response) : $response;
    }

    public function getPrintViews(string $path = null): JsonResponse
    {
        $response = $this->printService->getPrintViews($path);

        return ResponseHelper::createResponseFromArrayResponse($response)
            ->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function show(string $id, Request $request): JsonResponse
    {
        $printData = PrintData::query()
            ->whereKey($id)
            ->first();

        if (! $printData) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 404,
                data: ['id' => 'print data not found']
            );
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $printData->makeVisible('data')
        )->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function showHtml(Request $request, string $id): JsonResponse|View
    {
        $response = $this->printService->showHtml($request->all(), $id);

        return is_array($response) ? ResponseHelper::createResponseFromArrayResponse($response) : $response;
    }

    public function getPdf(Request $request, string $id): JsonResponse|Response
    {
        $response = $this->printService->showHtml($request->all(), $id, true);
        if (! array_key_exists('pdf', $response)) {
            return ResponseHelper::createResponseFromArrayResponse($response);
        }

        return response()->attachment($response['pdf']);
    }

    public function generatePdfFromView(GeneratePdfFromViewRequest $request): View|JsonResponse|Response
    {
        $response = $this->printService->generatePdfFromView($request->validated());

        if (! is_array($response)) {
            return $response;
        }

        if (array_key_exists('pdf', $response)) {
            return response()->attachment($response['pdf']);
        }

        $bulk = false;
        if ($response['status'] === 207 || ($response['responses'] ?? false)) {
            $bulk = true;
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: $response['status'],
            data: $bulk ? $response['responses'] :
                ($response['status'] >= 400 && $response['status'] < 500 ? $response['errors'] : $response['data']),
            statusMessage: array_key_exists('statusMessage', $response) ? $response['statusMessage'] : null,
            additions: array_diff_key(
                $response,
                array_flip([
                    'status', 'data', 'errors', 'statusMessage', 'responses',
                ])),
            bulk: $bulk
        )->setEncodingOptions(JSON_UNESCAPED_SLASHES);
    }

    public function update(UpdatePrintRequest $request, PrintDataService $printService): JsonResponse
    {
        $response = $printService->update($request->validated());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, PrintDataService $printService): JsonResponse
    {
        $response = $printService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
