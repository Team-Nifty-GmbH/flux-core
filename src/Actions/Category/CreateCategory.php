<?php

namespace FluxErp\Actions\Category;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateCategoryRequest;
use FluxErp\Models\Category;
use Illuminate\Support\Facades\Validator;

class CreateCategory extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateCategoryRequest())->rules();
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

    public function validate(): static
    {
        $this->data['sort_number'] = $this->data['sort_number'] ?? 0;
        $this->rules = array_merge(
            $this->rules,
            [
                'parent_id' => 'integer|nullable|exists:categories,id,model_type,' . $this->data['model_type'],
                'sort_number' => 'required|integer',
            ]
        );

        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Category());

        $this->data = $validator->validate();

        return $this;
    }
}
