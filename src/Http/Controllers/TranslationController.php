<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateTranslationRequest;
use FluxErp\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\TranslationLoader\LanguageLine;

class TranslationController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new LanguageLine();
    }

    public function create(CreateTranslationRequest $request, TranslationService $languageLineService): JsonResponse
    {
        $languageLine = $languageLineService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $languageLine,
            statusMessage: 'language line created'
        );
    }

    public function update(Request $request, TranslationService $languageLineService): JsonResponse
    {
        $response = $languageLineService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, TranslationService $languageLineService): JsonResponse
    {
        $response = $languageLineService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
