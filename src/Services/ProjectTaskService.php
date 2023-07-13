<?php

namespace FluxErp\Services;

use FluxErp\Actions\ProjectTask\CreateProjectTask;
use FluxErp\Actions\ProjectTask\DeleteProjectTask;
use FluxErp\Actions\ProjectTask\FinishProjectTask;
use FluxErp\Actions\ProjectTask\UpdateProjectTask;
use FluxErp\Helpers\ResponseHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ProjectTaskService
{
    public function create(array $data): array
    {
        try {
            $task = CreateProjectTask::make($data)->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 422,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 201,
            data: $task->refresh(),
            statusMessage: 'task created'
        );
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = [];
        foreach ($data as $key => $item) {
            try {
                $responses[] = ResponseHelper::createArrayResponse(
                    statusCode: 200,
                    data: $task = UpdateProjectTask::make($item)->validate()->execute(),
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

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'project task(s) updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        try {
            DeleteProjectTask::make(['id' => $id])->validate()->execute();
        } catch (ValidationException $e) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: $e->errors()
            );
        }

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'task deleted'
        );
    }

    public function finish(array $data): Model
    {
        return FinishProjectTask::make($data)->execute();
    }
}
