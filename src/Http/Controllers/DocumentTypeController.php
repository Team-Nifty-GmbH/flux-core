<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateDocumentTypeRequest;
use FluxErp\Models\DocumentType;
use FluxErp\Services\DocumentTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @deprecated
 */
class DocumentTypeController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(DocumentType::class);
    }

    public function create(CreateDocumentTypeRequest $request, DocumentTypeService $documentTypeService): JsonResponse
    {
        $documentType = $documentTypeService->create($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $documentType,
            statusMessage: 'document type created'
        );
    }

    public function delete(string $id, DocumentTypeService $documentTypeService): JsonResponse
    {
        $response = $documentTypeService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(Request $request, DocumentTypeService $documentTypeService): JsonResponse
    {
        $response = $documentTypeService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
