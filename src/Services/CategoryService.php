<?php

namespace FluxErp\Services;

use FluxErp\Helpers\Helper;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateCategoryRequest;
use FluxErp\Models\Category;

class CategoryService
{
    public function create(array $data): Category
    {
        $category = new Category($data);
        $category->save();

        return $category->refresh();
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateCategoryRequest(),
            service: $this,
            model: new Category()
        );

        foreach ($data as $item) {
            $category = Category::query()
                ->whereKey($item['id'])
                ->first();

            $category->fill($item);
            $category->save();

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $category->withoutRelations()->fresh(),
                additions: ['id' => $category->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'categories updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $category = Category::query()
            ->whereKey($id)
            ->first();

        if (! $category) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'category not found']
            );
        }

        if ($category->children->count() > 0) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['children' => 'category has children']
            );
        }

        if ($category->model()?->exists()) {
            return ResponseHelper::createArrayResponse(
                statusCode: 423,
                data: ['project_task' => 'project task with this category exists']
            );
        }

        $category->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'category deleted'
        );
    }

    public function validateItem(array $item, array $response): ?array
    {
        $category = Category::query()
            ->whereKey($item['id'])
            ->first();

        if ($item['parent_id'] ?? false) {
            $parentCategory = Category::query()
                ->whereKey($item['parent_id'])
                ->where('model_type', $category->model_type ?? $item['model_type'])
                ->first();

            if (! $parentCategory) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 422,
                    data: ['parent_id' => 'parent with model_type \'' . $category->model_type . '\' not found'],
                    additions: $response
                );
            }

            if (Helper::checkCycle(Category::class, $category, $item['parent_id'])) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 409,
                    data: ['parent_id' => 'cycle detected'],
                    additions: $response
                );
            }
        }

        return null;
    }
}
