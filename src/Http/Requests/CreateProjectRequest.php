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
                'uuid' => 'string|uuid|unique:projects,uuid',
                'contact_id' => 'integer|nullable|exists:contacts,id,deleted_at,NULL',
                'order_id' => 'integer|nullable|exists:contacts,id,deleted_at,NULL',
                'parent_id' => 'integer|nullable|exists:projects,id,deleted_at,NULL',
                'name' => 'required|string',
                'start_date' => 'date_format:Y-m-d|nullable',
                'end_date' => 'date_format:Y-m-d|nullable',
                'description' => 'string|nullable',
                'state' => [
                    'string',
                    ValidStateRule::make(ProjectState::class),
                ],
                'time_budget_hours' => 'numeric|nullable|min:0',
                'budget' => 'numeric|nullable|min:0',
            ],
        );
    }
}
