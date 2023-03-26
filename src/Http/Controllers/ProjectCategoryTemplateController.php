<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateProjectCategoryTemplateRequest;
use FluxErp\Models\ProjectCategoryTemplate;
use FluxErp\Services\ProjectCategoryTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectCategoryTemplateController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new ProjectCategoryTemplate();
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, ProjectCategoryTemplateService $templateService): JsonResponse
    {
        $validator = Validator::make($request->all(), (new CreateProjectCategoryTemplateRequest())->rules());
        $validator->addModel($this->model);

        if ($validator->fails()) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 422,
                data: $validator->errors()->toArray()
            );
        }

        $projectCategoryTemplate = $templateService->create($validator->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $projectCategoryTemplate,
            statusMessage: 'template created'
        );
    }

    public function update(Request $request, ProjectCategoryTemplateService $templateService): JsonResponse
    {
        $response = $templateService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, ProjectCategoryTemplateService $templateService): JsonResponse
    {
        $response = $templateService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }
}
