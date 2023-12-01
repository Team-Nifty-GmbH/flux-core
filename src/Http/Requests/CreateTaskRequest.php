<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Task;
use FluxErp\Rules\Numeric;
use FluxErp\States\Task\TaskState;
use Spatie\ModelStates\Validation\ValidStateRule;

class CreateTaskRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return array_merge(
            (new Task())->hasAdditionalColumnsValidationRules(),
            [
                'uuid' => 'string|uuid|unique:tasks,uuid',
                'project_id' => 'sometimes|required|integer|exists:projects,id,deleted_at,NULL',
                'responsible_user_id' => 'integer|nullable|exists:users,id,deleted_at,NULL',
                'name' => 'required|string',
                'description' => 'string|nullable',
                'start_date' => 'date_format:Y-m-d|nullable',
                'end_date' => 'date_format:Y-m-d|nullable|gte:start_date',
                'started_at' => 'date|nullable',
                'ended_at' => 'date|nullable|gte:started_at',
                'priority' => 'integer|nullable|min:0',
                'state' => [
                    'string',
                    ValidStateRule::make(TaskState::class),
                ],
                'time_budget_hours' => 'numeric|nullable|min:0',
                'budget' => 'numeric|nullable|min:0',

                'users' => 'array',
                'users.*' => 'required|integer|exists:users,id,deleted_at,NULL',

                'order_positions' => 'array',
                'order_positions.*.id' => 'required|integer|exists:order_positions,id,deleted_at,NULL',
                'order_positions.*.amount' => [
                    'required',
                    new Numeric(min: 0)
                ],
            ]
        );
    }
}
