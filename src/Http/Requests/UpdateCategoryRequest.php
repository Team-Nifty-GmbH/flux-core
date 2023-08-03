<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Category;

class UpdateCategoryRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            (new Category())->hasAdditionalColumnsValidationRules(),
            [
                'id' => 'required|integer|exists:categories,id',
                'parent_id' => 'integer|nullable|exists:categories,id',
                'name' => 'required|string',
                'sort_number' => 'integer|min:0',
                'is_active' => 'boolean',
            ],
        );
    }
}
