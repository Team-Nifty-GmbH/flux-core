<?php

namespace FluxErp\Rulesets\Task;

use FluxErp\Models\OrderPosition;
use FluxErp\Models\Project;
use FluxErp\Models\Task;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;

class ReplicateTaskRuleset extends FluxRuleset
{
    protected static ?string $model = Task::class;

    public static function getRules(): array
    {
        return array_merge(
            parent::getRules(),
            resolve_static(UserRuleset::class, 'getRules'),
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
            'name' => 'string|nullable|max:255',
            'description' => 'string|nullable',
            'start_date' => 'date|nullable',
            'due_date' => 'date|nullable|after_or_equal:start_date',
            'priority' => 'integer|nullable|min:0',
            'time_budget' => 'nullable|regex:/[0-9]*:[0-5][0-9]/',
            'budget' => 'numeric|nullable|min:0',
        ];
    }
}
