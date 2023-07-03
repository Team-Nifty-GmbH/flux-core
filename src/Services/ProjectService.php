<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateProjectRequest;
use FluxErp\Models\Category;
use FluxErp\Models\Project;
use FluxErp\States\Project\Done;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ProjectService
{
    public function create(array $data): array
    {
        if ($data['parent_id'] ?? false) {
            $parentProject = Project::query()
                ->whereKey($data['parent_id'])
                ->first();

            if (empty($parentProject)) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 404,
                    data: ['parent_id' => 'parent project not found']
                );
            }
        }

        $intArray = array_filter($data['categories'], function ($value) {
            return is_int($value) && $value > 0;
        });

        $categories = Category::query()
            ->whereKey($data['category_id'])
            ->with('children:id,parent_id')
            ->first();
        $categories = array_column(to_flat_tree($categories->children->toArray()), 'id');

        $diff = array_diff($intArray, $categories);
        if (count($diff) > 0 || count($categories) === 0) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['categories' => array_values($diff)],
                statusMessage: 'categories not found'
            );
        }

        $project = new Project($data);
        $project->save();

        return ResponseHelper::createArrayResponse(
            statusCode: 201,
            data: $project,
            statusMessage: 'project created'
        );
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateProjectRequest(),
            service: $this,
            model: new Project()
        );

        foreach ($data as $item) {
            $project = Project::query()
                ->whereKey($item['id'])
                ->first();

            $project->fill($item);
            $project->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $project->withoutRelations()->fresh(),
                additions: ['id' => $project->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'projects updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $project = Project::query()
            ->whereKey($id)
            ->first();

        if (! $project) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'project not found']
            );
        }

        if ($project->children->count() > 0) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['children' => 'project has children']
            );
        }

        $project->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'project deleted'
        );
    }

    public function finishProject(array $data): Model
    {
        $project = Project::query()
            ->whereKey($data['id'])
            ->first();

        $project->state = $data['finish'] ? Done::class : Project::getDefaultStateFor('state');
        $project->save();

        return $project;
    }

    public function validateItem(array $item, array $response): ?array
    {
        if (array_key_exists('categories', $item)) {
            if (is_array($item['categories'][0])) {
                $intArray = Arr::pluck($item['categories'], 'id');
            } else {
                $intArray = array_filter($item['categories'], function ($value) {
                    return is_numeric($value) && $value > 0;
                });
                $intArray = array_map('intval', $intArray);
            }

            $project = Project::query()
                ->whereKey($item['id'])
                ->with(['tasks' => ['categories:id'], 'categories:id'])
                ->first();

            $projectCategories = $project
                ->category
                ?->children()
                ->with('children:id,parent_id')
                ->get()
                ->toArray();
            $categories = $projectCategories ? array_column(to_flat_tree($projectCategories), 'id') : [];

            $diff = array_diff($intArray, $categories);
            if (count($diff) > 0) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 404,
                    data: [
                        'categories' => 'categories not found: \'' . implode(',', array_values($diff)) . '\'',
                    ],
                    additions: $response);
            }

            $projectTaskCategories = [];
            $project->tasks->each(function ($task) use (&$projectTaskCategories) {
                $projectTaskCategories = array_merge(
                    $projectTaskCategories,
                    $task->categories->pluck('id')->toArray()
                );
            });

            if (! empty(array_diff($projectTaskCategories, $intArray))) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 409,
                    data: ['categories' => 'project task with different category exists'],
                    additions: $response
                );
            }
        }

        return null;
    }
}
