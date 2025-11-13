<?php

namespace FluxErp\Rulesets\Task;

use FluxErp\Models\OrderPosition;
use FluxErp\Models\Project;
use FluxErp\Models\Task;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rules\ValidStateRule;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\States\Task\TaskState;
use Illuminate\Validation\Rule;

class UpdateTaskRuleset extends FluxRuleset
{
    protected static ?string $model = Task::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(UserRuleset::class, 'getRules'),
            resolve_static(OrderPositionRuleset::class, 'getRules'),
            resolve_static(CategoryRuleset::class, 'getRules'),
            resolve_static(TagRuleset::class, 'getRules')
        );
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Task::class]),
            ],
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
            'order_position_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => OrderPosition::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'description' => 'string|nullable',
            'start_date' => 'present|date|nullable',
            'start_time' => [
                'nullable',
                'exclude_if:start_date,null',
                Rule::anyOf(['date_format:H:i', 'date_format:H:i:s']),
            ],
            'due_date' => 'present|date|nullable|after_or_equal:start_date',
            'due_time' => [
                'nullable',
                'exclude_if:due_date,null',
                Rule::anyOf(['date_format:H:i', 'date_format:H:i:s']),
            ],
            'has_start_reminder' => 'boolean',
            'start_reminder_minutes_before' => 'nullable|integer|min:0',
            'has_due_reminder' => 'boolean',
            'due_reminder_minutes_before' => 'nullable|integer|min:0',
            'priority' => 'sometimes|required|integer|min:0',
            'state' => [
                'string',
                ValidStateRule::make(TaskState::class),
            ],
            'time_budget' => 'nullable|regex:/[0-9]*:[0-5][0-9]/',
            'budget' => [
                'nullable',
                app(Numeric::class, ['min' => 0]),
            ],
        ];
    }
}
