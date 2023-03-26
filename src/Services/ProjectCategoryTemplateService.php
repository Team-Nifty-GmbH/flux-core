<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateProjectCategoryTemplateRequest;
use FluxErp\Models\Category;
use FluxErp\Models\ProjectCategoryTemplate;
use FluxErp\Models\ProjectTask;
use Illuminate\Support\Arr;

class ProjectCategoryTemplateService
{
    public function create(array $data): ProjectCategoryTemplate
    {
        $template = new ProjectCategoryTemplate($data);
        $template->save();

        return $template;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateProjectCategoryTemplateRequest(),
            service: $this,
            model: new ProjectCategoryTemplate()
        );

        foreach ($data as $item) {
            $template = ProjectCategoryTemplate::query()
                ->whereKey($item['id'])
                ->first();

            $template->fill($item);
            $template->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $template->withoutRelations()->fresh(),
                additions: ['id' => $template->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'project category templates updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $template = ProjectCategoryTemplate::query()
            ->whereKey($id)
            ->first();

        if (! $template) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'template not found']
            );
        }

        if ($template->projects()->withTrashed()->exists()) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['project' => 'template referenced by a project']
            );
        }

        $template->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'template deleted'
        );
    }

    public function validateItem(array $item, array $response): ?array
    {
        if (! array_key_exists('categories', $item)) {
            return null;
        }

        $template = ProjectCategoryTemplate::query()
            ->whereKey($item['id'])
            ->first();

        if (is_array($item['categories'][0])) {
            $intArray = Arr::pluck($item['categories'], 'id');
        } else {
            $intArray = $item['categories'];
        }

        $categories = Category::query()
            ->whereIntegerInRaw('id', $intArray)
            ->where('model_type', ProjectTask::class)
            ->get()
            ->pluck('id')
            ->toArray();

        $projectTaskCategories = array_unique(
            Arr::flatten(Arr::pluck($template->projects()->with('tasks')->get()->toArray(), 'tasks.*.category_id'))
        );

        if (! empty(array_diff($projectTaskCategories, $categories))) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['categories' => 'at least one category referenced by project task'],
                additions: $response
            );
        }

        return null;
    }
}
