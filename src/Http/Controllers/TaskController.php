<?php

namespace FluxErp\Http\Controllers;

use FluxErp\Actions\Task\CreateTask;
use FluxErp\Actions\Task\DeleteTask;
use FluxErp\Actions\Task\FinishTask;
use FluxErp\Actions\Task\UpdateTask;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TaskController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->model = app(Task::class);
    }

    public function create(Request $request): JsonResponse
    {
        try {
            $task = CreateTask::make($request->all())->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 422,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 201,
            data: $task,
            statusMessage: __('task created')
        );
    }

    public function delete(string $id): JsonResponse
    {
        try {
            DeleteTask::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createResponseFromBase(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createResponseFromBase(
            statusCode: 204,
            statusMessage: __('task deleted')
        );
    }

    public function finish(Request $request): JsonResponse
    {
        $task = FinishTask::make($request->all())
            ->validate()
            ->execute();

        return ResponseHelper::createResponseFromBase(
            statusCode: 200,
            data: $task,
            statusMessage: 'task ' . $request->finish ? 'finished' : 'reopened'
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
                    data: $task = UpdateTask::make($item)->validate()->execute(),
                    additions: ['id' => $task->id]
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
                    ['statusMessage' => __('task updated')]
                )
            ) :
            ResponseHelper::createResponseFromBase(
                statusCode: $statusCode,
                data: $responses,
                statusMessage: $statusCode === 422 ? null : __('task(s) updated'),
                bulk: true
            );
    }
}
