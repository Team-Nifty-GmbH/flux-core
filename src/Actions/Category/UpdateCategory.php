<?php

namespace FluxErp\Actions\Category;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Helpers\Helper;
use FluxErp\Http\Requests\UpdateCategoryRequest;
use FluxErp\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateCategory implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateCategoryRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'category.update';
    }

    public static function description(): string|null
    {
        return 'update category';
    }

    public static function models(): array
    {
        return [Category::class];
    }

    public function execute(): Model
    {
        $category = Category::query()
            ->whereKey($this->data['id'])
            ->first();

        $category->fill($this->data);
        $category->save();

        return $category->withoutRelations()->fresh();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Category());

        $this->data = $validator->validate();

        if ($this->data['parent_id'] ?? false) {
            $category = Category::query()
                ->whereKey($this->data['id'])
                ->first();

            $parentCategory = Category::query()
                ->whereKey($this->data['parent_id'])
                ->where('model_type', $category->model_type ?? $this->data['model_type'])
                ->first();

            if (! $parentCategory) {
                throw ValidationException::withMessages([
                    'parent_id' => [
                        __(
                            'Parent with model_type \':modelType\' not found',
                            ['modelType' => $category->model_type]
                        )
                    ]
                ])->errorBag('updateProjectTask');
            }

            if (Helper::checkCycle(Category::class, $category, $this->data['parent_id'])) {
                throw ValidationException::withMessages([
                    'parent_id' => [__('Cycle detected')]
                ])->errorBag('updateProjectTask');
            }
        }

        return $this;
    }
}
