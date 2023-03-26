<?php

namespace FluxErp\Http\Requests;

class CreateProjectRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'parent_id' => 'integer|nullable|exists:projects,id,deleted_at,NULL',
            'project_category_template_id' => 'required|integer|exists:project_category_templates,id',
            'project_name' => 'required|string',
            'display_name' => 'string|nullable',
            'release_date' => 'required|date_format:Y-m-d',
            'deadline' => 'date_format:Y-m-d|nullable',
            'description' => 'string|nullable',
            'categories' => 'required|array',
        ];
    }
}
