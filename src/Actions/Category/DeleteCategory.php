<?php

namespace FluxErp\Actions\Category;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Category;
use FluxErp\Rulesets\Category\DeleteCategoryRuleset;
use Illuminate\Validation\ValidationException;

class DeleteCategory extends FluxAction
{
    public static function models(): array
    {
        return [Category::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteCategoryRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Category::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];
        $category = resolve_static(Category::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        if ($category->children->count() > 0) {
            $errors += [
                'children' => [__('Category has children')],
            ];
        }

        if ($category->model()?->exists()) {
            $errors += [
                'model' => [__('Model with this category exists')],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('deleteCategory')
                ->status(423);
        }
    }
}
