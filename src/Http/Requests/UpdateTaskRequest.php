<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Task;
use FluxErp\Rules\ExistsWithIgnore;
use FluxErp\Rules\Numeric;
use FluxErp\States\Task\TaskState;
use Spatie\ModelStates\Validation\ValidStateRule;

class UpdateTaskRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            (new Task())->hasAdditionalColumnsValidationRules(),
            [
                'id' => 'required|integer|exists:tasks,id,deleted_at,NULL',
                'project_id' => [
                    'integer',
                    'nullable',
                    (new ExistsWithIgnore('projects', 'id'))->whereNull('deleted_at'),
                ],
                'responsible_user_id' => [
                    'integer',
                    'nullable',
                    (new ExistsWithIgnore('users', 'id'))->whereNull('deleted_at'),
                ],
                'name' => 'sometimes|required|string',
                'description' => 'string|nullable',
                'start_date' => 'present|date_format:Y-m-d|nullable',
                'due_date' => 'present|date_format:Y-m-d|nullable|after_or_equal:start_date',
                'priority' => 'integer|nullable|min:0',
                'state' => [
                    'string',
                    ValidStateRule::make(TaskState::class),
                ],
                'time_budget' => 'nullable|regex:/[0-9]*:[0-5][0-9]/',
                'budget' => 'numeric|nullable|min:0',

                'users' => 'array',
                'users.*' => 'required|integer|exists:users,id,deleted_at,NULL',

                'order_positions' => 'array',
                'order_positions.*.id' => 'required|integer|exists:order_positions,id,deleted_at,NULL',
                'order_positions.*.amount' => [
                    'required',
                    new Numeric(min: 0),
                ],

                'tags' => 'array',
                'tags.*' => 'integer|exists:tags,id',
            ],
        );
    }
}
