<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\Project\CreateProject;
use FluxErp\Actions\Project\DeleteProject;
use FluxErp\Actions\Project\FinishProject;
use FluxErp\Actions\Project\UpdateProject;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProjectController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(Project::class);
    }

    public function create(Request $request): JsonResponse
    {
        try {
            $project = CreateProject::make($request->all())->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 422,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $project,
            statusMessage: __('project created')
        );
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->all();
        if (! array_is_list($data)) {
            $data = [$request->all()];
        }

        $responses = [];
        foreach ($data as $key => $item) {
            try {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 200,
                    data: $project = UpdateProject::make($item)->validate()->execute(),
                    additions: ['id' => $project->id]
                );
            } catch (ValidationException $e) {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 422,
                    data: $e->errors(),
                    additions: [
                        'id' => array_key_exists('id', $item) ? $item['id'] : null,
                    ]
                );

                unset($data[$key]);
            }
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        $bulk = count($responses) > 1;

        return ! $bulk ?
            ResponseHelper::createResponseFromArrayResponse(
                array_merge(
                    array_shift($responses),
                    ['statusMessage' => __('project updated')]
                )
            ) :
            ResponseHelper::createResponseFromBase(
                statusCode: $statusCode,
                data: $responses,
                statusMessage: $statusCode === 422 ? null : __('project(s) updated'),
                bulk: true
            );
    }

    public function delete(string $id): JsonResponse
    {
        try {
            DeleteProject::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createResponseFromBase(
                statusCode: array_key_exists('id', $e->errors()) ? 404 : 423,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 204,
            statusMessage: __('project deleted')
        );
    }

    public function finish(Request $request): JsonResponse
    {
        $project = FinishProject::make($request->all())
            ->validate()
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $project,
            statusMessage: 'project ' . ($request->finish ? 'finished' : 'reopened')
        );
    }
}
