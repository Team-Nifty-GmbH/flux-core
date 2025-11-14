<?php

namespace FluxErp\Rulesets\Task;

use FluxErp\Models\OrderPosition;
use FluxErp\Models\Project;
use FluxErp\Models\Task;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rules\ValidStateRule;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\States\Task\TaskState;
use Illuminate\Validation\Rule;

class CreateTaskRuleset extends FluxRuleset
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
            'order_position_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => OrderPosition::class]),
            ],
            'model_type' => [
                'required_with:model_id',
                'string',
                'max:255',
                'nullable',
                app(MorphClassExists::class),
            ],
            'model_id' => [
                'required_with:model_type',
                'integer',
                'nullable',
                app(MorphExists::class),
            ],
            'name' => 'required|string|max:255',
            'description' => 'string|nullable',
            'start_date' => 'date|nullable',
            'start_time' => [
                'nullable',
                'exclude_if:start_date,null',
                Rule::anyOf(['date_format:H:i', 'date_format:H:i:s']),
            ],
            'start_reminder_minutes_before' => [
                'required_if_accepted:has_start_reminder',
                'exclude_if:has_start_reminder,false',
                'integer',
                'min:0',
            ],
            'due_date' => 'date|nullable|after_or_equal:start_date',
            'due_time' => [
                'nullable',
                'exclude_if:due_date,null',
                Rule::anyOf(['date_format:H:i', 'date_format:H:i:s']),
            ],
            'due_reminder_minutes_before' => [
                'required_if_accepted:has_due_reminder',
                'exclude_if:has_due_reminder,false',
                'integer',
                'min:0',
            ],
            'priority' => 'integer|nullable|min:0',
            'state' => [
                'string',
                ValidStateRule::make(TaskState::class),
            ],
            'time_budget' => 'nullable|regex:/[0-9]*:[0-5][0-9]/',
            'budget' => [
                'nullable',
                app(Numeric::class, ['min' => 0]),
            ],
            'has_start_reminder' => 'boolean',
            'has_due_reminder' => 'boolean',
        ];
    }
}
