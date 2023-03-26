<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateProjectRequest;
use FluxErp\Http\Requests\FinishProjectRequest;
use FluxErp\Models\Project;
use FluxErp\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Project();
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, ProjectService $projectService): JsonResponse
    {
        $validator = Validator::make($request->all(), (new CreateProjectRequest())->rules());
        $validator->addModel($this->model);

        if ($validator->fails()) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 422,
                data: $validator->errors()->toArray()
            );
        }

        $response = $projectService->create($validator->validated());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(Request $request, ProjectService $projectService): JsonResponse
    {
        $response = $projectService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, ProjectService $projectService): JsonResponse
    {
        $response = $projectService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function finish(FinishProjectRequest $request, ProjectService $projectService): JsonResponse
    {
        $project = $projectService->finishProject($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $project,
            statusMessage: 'project ' . ($request->validated()['finish'] ? 'finished' : 'reopened')
        );
    }
}
