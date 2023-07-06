<?php

namespace FluxErp\Actions\Category;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DeleteCategory implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:categories',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'category.delete';
    }

    public static function description(): string|null
    {
        return 'delete category';
    }

    public static function models(): array
    {
        return [Category::class];
    }

    public function execute()
    {
        return Category::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        $errors = [];
        $category = Category::query()
            ->whereKey($this->data['id'])
            ->first();

        if ($category->children->count() > 0) {
            $errors += [
                'children' => [__('Category has children')]
            ];
        }

        if ($category->model()?->exists()) {
            $errors += [
                'project_task' => [__('Project task with this category exists')]
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('deleteCategory');
        }

        return $this;
    }
}
