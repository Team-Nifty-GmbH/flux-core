<?php

namespace FluxErp\Actions\Category;

use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Models\Category;
use FluxErp\Rulesets\Category\UpdateCategoryRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateCategory extends FluxAction
{
    public static function models(): array
    {
        return [Category::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateCategoryRuleset::class;
    }

    public function performAction(): Model
    {
        $category = resolve_static(Category::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $category->fill($this->data);
        $category->save();

        return $category->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if ($this->data['parent_id'] ?? false) {
            $category = resolve_static(Category::class, 'query')
                ->whereKey($this->data['id'])
                ->first();

            $parentCategory = resolve_static(Category::class, 'query')
                ->whereKey($this->data['parent_id'])
                ->where('model_type', $category->model_type ?? $this->data['model_type'])
                ->first();

            if (! $parentCategory) {
                throw ValidationException::withMessages([
                    'parent_id' => [
                        __(
                            'Parent with model_type \':modelType\' not found',
                            ['modelType' => $category->model_type]
                        ),
                    ],
                ])->errorBag('updateProjectTask');
            }

            if (Helper::checkCycle(Category::class, $category, $this->data['parent_id'])) {
                throw ValidationException::withMessages([
                    'parent_id' => ['Cycle detected'],
                ])->errorBag('updateCategory');
            }
        }
    }
}
