<?php

namespace FluxErp\Rulesets\Task;

use FluxErp\Models\Project;
use FluxErp\Models\Task;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\States\Task\TaskState;
use Spatie\ModelStates\Validation\ValidStateRule;

class UpdateTaskRuleset extends FluxRuleset
{
    protected static ?string $model = Task::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Task::class),
            ],
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
        ];
    }

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(UserRuleset::class, 'getRules'),
            resolve_static(OrderPositionRuleset::class, 'getRules'),
            resolve_static(TagRuleset::class, 'getRules')
        );
    }
}
