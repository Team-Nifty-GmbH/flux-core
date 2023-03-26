<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreatePresentationRequest;
use FluxErp\Models\Presentation;
use FluxErp\Services\PresentationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class PresentationController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Presentation();
    }

    public function showHtmlPublic(string $uuid): JsonResponse|View
    {
        $presentation = Presentation::query()
            ->where('uuid', $uuid)
            ->where('is_public', true)
            ->first();

        if (! $presentation || count($presentation->printData) == 0) {
            abort(404);
        }

        $presentationService = new PresentationService();
        $response = $presentationService->showHtml($presentation->id);

        return is_array($response) ? ResponseHelper::createResponseFromArrayResponse($response) : $response;
    }

    public function showHtml(string $id): JsonResponse|View
    {
        $presentationService = new PresentationService();
        $response = $presentationService->showHtml($id);

        return is_array($response) ? ResponseHelper::createResponseFromArrayResponse($response) : $response;
    }

    public function getPdf(string $id): JsonResponse|Response
    {
        $presentationService = new PresentationService();

        $response = $presentationService->showHtml($id, true);
        if (! array_key_exists('pdf', $response)) {
            return ResponseHelper::createResponseFromArrayResponse($response);
        }

        return response()->attachment($response['pdf']);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, PresentationService $presentationService): JsonResponse
    {
        $validator = Validator::make($request->all(), (new CreatePresentationRequest())->rules());
        $validator->addModel($this->model);

        if ($validator->fails()) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 422,
                data: $validator->errors()->toArray()
            );
        }

        $response = $presentationService->create($validator->validated());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(Request $request, PresentationService $presentationService): JsonResponse
    {
        $response = $presentationService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, PresentationService $presentationService): JsonResponse
    {
        $response = $presentationService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
