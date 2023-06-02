<?php

namespace FluxErp\Http\Requests;

class CreateCategoryRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'model_type' => 'required|string',
            'name' => 'required|string',
            'parent_id' => 'integer|nullable|exists:categories,id',
            'is_active' => 'boolean',
        ];
    }
}
