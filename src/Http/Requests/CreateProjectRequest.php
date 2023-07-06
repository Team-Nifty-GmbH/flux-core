<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Project;
use FluxErp\States\Project\ProjectState;
use Spatie\ModelStates\Validation\ValidStateRule;

class CreateProjectRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            (new Project())->hasAdditionalColumnsValidationRules(),
            [
                'parent_id' => 'integer|nullable|exists:projects,id,deleted_at,NULL',
                'category_id' => 'required|integer|exists:categories,id',
                'project_name' => 'required|string',
                'display_name' => 'string|nullable',
                'release_date' => 'required|date_format:Y-m-d',
                'deadline' => 'date_format:Y-m-d|nullable',
                'description' => 'string|nullable',
                'state' => [
                    'string',
                    ValidStateRule::make(ProjectState::class),
                ],
                'categories' => 'required|array',
            ],
        );
    }
}
