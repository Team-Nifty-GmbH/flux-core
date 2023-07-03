<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Project;
use FluxErp\Rules\ExistsWithIgnore;
use FluxErp\States\Project\ProjectState;
use Spatie\ModelStates\Validation\ValidStateRule;

class UpdateProjectRequest extends BaseFormRequest
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
                'id' => 'required|integer|exists:projects,id,deleted_at,NULL',
                'category_id' => [
                    'integer',
                    new ExistsWithIgnore('categories', 'id'),
                ],
                'project_name' => 'sometimes|string',
                'display_name' => 'sometimes|string|nullable',
                'release_date' => 'sometimes|date_format:Y-m-d',
                'deadline' => 'sometimes|date_format:Y-m-d|nullable',
                'description' => 'sometimes|string|nullable',
                'state' => [
                    'string',
                    ValidStateRule::make(ProjectState::class),
                ],
                'categories' => 'sometimes|required|array',
            ],
        );
    }
}
