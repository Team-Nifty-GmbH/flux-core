<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Http\Requests\CreateProjectTaskRequest;
use FluxErp\Http\Requests\FinishProjectTaskRequest;
use FluxErp\Models\ProjectTask;
use FluxErp\Services\ProjectTaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectTaskController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new ProjectTask();
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, ProjectTaskService $projectTaskService): JsonResponse
    {
        $validator = Validator::make($request->all(), (new CreateProjectTaskRequest())->rules());
        $validator->addModel($this->model);

        if ($validator->fails()) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 422,
                data: $validator->errors()->toArray()
            );
        }

        $response = $projectTaskService->create($validator->validated());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function update(Request $request, ProjectTaskService $projectTaskService): JsonResponse
    {
        $response = $projectTaskService->update($request->all());

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function delete(string $id, ProjectTaskService $projectTaskService): JsonResponse
    {
        $response = $projectTaskService->delete($id);

        return ResponseHelper::createResponseFromArrayResponse($response);
    }

    public function finish(FinishProjectTaskRequest $request, ProjectTaskService $projectTaskService): JsonResponse
    {
        $projectTask = $projectTaskService->finish($request->validated());

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $projectTask,
            statusMessage: 'task ' . $request->validated()['finish'] ? 'finished' : 'reopened'
        );
    }
}
