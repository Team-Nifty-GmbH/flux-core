<?php

namespace FluxErp\Actions\Category;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Category;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\Category\CreateCategoryRuleset;

class CreateCategory extends FluxAction
{
    public static function models(): array
    {
        return [Category::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateCategoryRuleset::class;
    }

    public function performAction(): Category
    {
        $category = app(Category::class, ['attributes' => $this->data]);
        $category->save();

        return $category->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['sort_number'] = $this->data['sort_number'] ?? 0;
        $this->rules = array_merge(
            $this->rules,
            [
                'parent_id' => [
                    'integer',
                    'nullable',
                    (new ModelExists(Category::class))
                        ->where('model_type', $this->data['model_type'] ?? null),
                ],
            ]
        );
    }
}
