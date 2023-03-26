<?php

namespace FluxErp\Http\Requests;

class UpdateProjectCategoryTemplateRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:project_category_templates,id',
            'name' => 'required|string',
            'categories' => 'sometimes|array',
        ];
    }
}
