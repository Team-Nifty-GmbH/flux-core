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
                'order_id' => 'integer|nullable|exists:orders,id,deleted_at,NULL',
                'responsible_user_id' => 'integer|nullable|exists:users,id,deleted_at,NULL',
                'parent_id' => 'integer|nullable|exists:projects,id,deleted_at,NULL',
                'project_number' => 'nullable|string',
                'name' => 'required|string',
                'start_date' => 'date_format:Y-m-d|nullable',
                'end_date' => 'date_format:Y-m-d|nullable',
                'description' => 'string|nullable',
                'state' => [
                    'string',
                    ValidStateRule::make(ProjectState::class),
                ],
                'time_budget' => 'nullable|regex:/[0-9]*:[0-5][0-9]/',
                'budget' => 'numeric|nullable|min:0',
            ],
        );
    }
}
