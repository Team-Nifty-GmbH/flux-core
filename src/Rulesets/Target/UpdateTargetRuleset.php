<?php

namespace FluxErp\Rulesets\Target;

use FluxErp\Models\Target;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class UpdateTargetRuleset extends FluxRuleset
{
    protected static ?string $model = Target::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Target::class])
                    ->whereNull('parent_id'),
            ],
            'name' => 'sometimes|required|string|max:255',
            'target_value' => [
                'sometimes',
                'required',
                app(Numeric::class),
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'timeframe_column' => 'sometimes|required|string',
            'aggregate_type' => 'sometimes|required_with:aggregate_column|string',
            'aggregate_column' => 'sometimes|required_with:aggregate_type|string',
            'owner_column' => 'sometimes|required|string',
            'priority' => 'nullable|integer|min:0|max:255',
            'is_group_target' => 'boolean',
            'users' => 'nullable|array',
            'users.*.user_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'users.*.target_share' => [
                'required',
                'numeric',
                'min:0',
            ],
            'users.*.is_percentage' => [
                'required',
                'boolean',
            ],
        ];
    }
}
