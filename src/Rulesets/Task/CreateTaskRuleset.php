<?php

namespace FluxErp\Rulesets\Task;

use FluxErp\Models\Project;
use FluxErp\Models\Task;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\States\Task\TaskState;
use Spatie\ModelStates\Validation\ValidStateRule;

class CreateTaskRuleset extends FluxRuleset
{
    protected static ?string $model = Task::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:tasks,uuid',
            'project_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => Project::class]),
            ],
            'responsible_user_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'model_type' => [
                'required_with:model_id',
                'string',
                'nullable',
                app(MorphClassExists::class),
            ],
            'model_id' => [
                'required_with:model_type',
                'integer',
                'nullable',
                app(MorphExists::class),
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
