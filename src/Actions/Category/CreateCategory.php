<?php

namespace FluxErp\Actions\Category;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateCategoryRequest;
use FluxErp\Models\Category;
use Illuminate\Support\Facades\Validator;

class CreateCategory extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateCategoryRequest())->rules();
    }

    public static function models(): array
    {
        return [Category::class];
    }

    public function performAction(): Category
    {
        $category = new Category($this->data);
        $category->save();

        return $category->refresh();
    }

    public function validateData(): void
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
    }
}
