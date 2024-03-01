<?php

namespace FluxErp\Actions\Category;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Category;
use FluxErp\Rulesets\Category\DeleteCategoryRuleset;
use Illuminate\Validation\ValidationException;

class DeleteCategory extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteCategoryRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Category::class];
    }

    public function performAction(): ?bool
    {
        return app(Category::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];
        $category = app(Category::class)->query()
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
            throw ValidationException::withMessages($errors)->errorBag('deleteCategory');
        }
    }
}
