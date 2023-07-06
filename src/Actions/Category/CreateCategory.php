<?php

namespace FluxErp\Actions\Category;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateCategoryRequest;
use FluxErp\Models\Category;
use Illuminate\Support\Facades\Validator;

class CreateCategory implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateCategoryRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'category.create';
    }

    public static function description(): string|null
    {
        return 'create category';
    }

    public static function models(): array
    {
        return [Category::class];
    }

    public function execute(): Category
    {
        $category = new Category($this->data);
        $category->save();

        return $category->refresh();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
