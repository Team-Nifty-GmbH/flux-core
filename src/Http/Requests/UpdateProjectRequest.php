<?php

namespace FluxErp\Http\Requests;

use FluxErp\Rules\ExistsWithIgnore;

class UpdateProjectRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:projects,id,deleted_at,NULL',
            'project_category_template_id' => [
                'integer',
                new ExistsWithIgnore('project_category_templates', 'id'),
            ],
            'project_name' => 'sometimes|string',
            'display_name' => 'sometimes|string|nullable',
            'release_date' => 'sometimes|date_format:Y-m-d',
            'deadline' => 'sometimes|date_format:Y-m-d|nullable',
            'description' => 'sometimes|string|nullable',
            'is_done' => 'sometimes|boolean',
            'categories' => 'sometimes|required|array',
        ];
    }
}
