<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\OrderPosition;
use FluxErp\Models\Project;
use FluxErp\Models\Tag;
use FluxErp\Models\Task;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\States\Task\TaskState;
use Spatie\ModelStates\Validation\ValidStateRule;

class CreateTaskRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            (new Task())->hasAdditionalColumnsValidationRules(),
            [
                'uuid' => 'string|uuid|unique:tasks,uuid',
                'project_id' => [
                    'integer',
                    'nullable',
                    new ModelExists(Project::class),
                ],
                'responsible_user_id' => [
                    'integer',
                    'nullable',
                    new ModelExists(User::class),
                ],
                'name' => 'required|string',
                'description' => 'string|nullable',
                'start_date' => 'date_format:Y-m-d|nullable',
                'due_date' => 'date_format:Y-m-d|nullable|after_or_equal:start_date',
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
                'order_positions.*.id' => [
                    'required',
                    'integer',
                    new ModelExists(OrderPosition::class),
                ],
                'order_positions.*.amount' => [
                    'required',
                    new Numeric(min: 0),
                ],

                'tags' => 'array',
                'tags.*' => [
                    'required',
                    'integer',
                    (new ModelExists(Tag::class))->where('type', Task::class),
                ],
            ]
        );
    }
}
