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
                'contact_id' => [
                    'integer',
                    'nullable',
                    (new ExistsWithIgnore('contacts', 'id'))->whereNull('deleted_at'),
                ],
                'order_id' => [
                    'integer',
                    'nullable',
                    (new ExistsWithIgnore('orders', 'id'))->whereNull('deleted_at'),
                ],
                'name' => 'sometimes|string',
                'start_date' => 'sometimes|date_format:Y-m-d|nullable',
                'end_date' => 'sometimes|date_format:Y-m-d|nullable',
                'description' => 'sometimes|string|nullable',
                'state' => [
                    'string',
                    ValidStateRule::make(ProjectState::class),
                ],
                'progress' => 'sometimes|integer|nullable|min:0|max:100',
                'time_budget_hours' => 'sometimes|numeric|nullable',
                'budget' => 'sometimes|numeric|nullable',
            ],
        );
    }
}
