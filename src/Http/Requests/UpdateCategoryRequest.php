<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Category;
use FluxErp\Rules\ModelExists;

class UpdateCategoryRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            (new Category())->hasAdditionalColumnsValidationRules(),
            [
                'id' => [
                    'required',
                    'integer',
                    new ModelExists(Category::class),
                ],
                'parent_id' => [
                    'integer',
                    'nullable',
                    new ModelExists(Category::class),
                ],
                'name' => 'required|string',
                'sort_number' => 'integer|min:0',
                'is_active' => 'boolean',
            ],
        );
    }
}
