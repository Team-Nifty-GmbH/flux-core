<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateLanguageRequest;
use FluxErp\Models\Language;
use FluxErp\Services\LanguageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LanguageController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Language();
    }

    public function create(CreateLanguageRequest $request, LanguageService $languageService): JsonResponse
    {
        $language = $languageService->create($request->validated());

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
