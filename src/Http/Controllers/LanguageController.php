<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Language;
use FluxErp\Services\LanguageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LanguageController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(Language::class);
    }

    public function create(Request $request, LanguageService $languageService): JsonResponse
    {
        $language = $languageService->create($request->all());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $language,
            statusMessage: 'language created'
        );
    }

    public function update(Request $request, LanguageService $languageService): JsonResponse
    {
        $response = $languageService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, LanguageService $languageService): JsonResponse
    {
        $response = $languageService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
