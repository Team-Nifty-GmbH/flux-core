<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateProjectTaskRequest;
use FluxErp\Models\Project;
use FluxErp\Models\ProjectTask;
use Illuminate\Database\Eloquent\Model;

class ProjectTaskService
{
    public function create(array $data): array
    {
        $task = new ProjectTask();

        $project = Project::query()
            ->whereKey($data['project_id'])
            ->first();

        if (($data['category_id'] ?? false) && ! $project->categories()->whereKey($data['category_id'])->exists()) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['category_id' => 'category not found in project categories']
            );
        }

        $task->fill($data);
        $task->save();

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

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateProjectTaskRequest(),
            service: $this,
            model: new ProjectTask()
        );

        foreach ($data as $item) {
            $task = ProjectTask::query()
                ->whereKey($item['id'])
                ->first();

            $task->fill($item);
            $task->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $task->withoutRelations()->fresh(),
                additions: ['id' => $task->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'project tasks updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $task = ProjectTask::query()
            ->whereKey($id)
            ->first();

        if (! $task) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'task not found']
            );
        }

        $task->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'task deleted'
        );
    }

    public function finish(array $data): Model
    {
        $task = ProjectTask::query()
            ->whereKey($data['id'])
            ->first();

        $task->is_done = $data['finish'];
        $task->save();

        return $task;
    }

    public function validateItem(array $item, array $response): ?array
    {
        $project = ($item['project_id'] ?? false)
            ? Project::query()->whereKey($item['project_id'])->first()
            : ProjectTask::query()
                ->whereKey($item['id'])
                ->first()->project;

        if (array_key_exists('category_id', $item)) {
            if (! $project->categories()->whereKey($item['category_id'])->exists()) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 404,
                    data: ['category_id' => 'project category not found'],
                    additions: $response
                );
            }
        }

        return null;
    }
}
