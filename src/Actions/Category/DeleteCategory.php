<?php

namespace FluxErp\Actions\Category;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Category;
use Illuminate\Validation\ValidationException;

class DeleteCategory extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:categories,id',
        ];
    }

    public static function models(): array
    {
        return [Category::class];
    }

    public function execute(): bool|null
    {
        return Category::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validate(): static
    {
        parent::validate();

        $errors = [];
        $category = Category::query()
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

        return $this;
    }
}
