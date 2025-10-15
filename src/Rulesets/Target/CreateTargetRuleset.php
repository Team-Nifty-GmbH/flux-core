<?php

namespace FluxErp\Rulesets\Target;

use FluxErp\Contracts\Targetable;
use FluxErp\Models\Target;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class CreateTargetRuleset extends FluxRuleset
{
    protected static ?string $model = Target::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:targets,uuid',
            'target_value' => [
                'required',
                app(Numeric::class),
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'model_type' => [
                'required',
                'string',
                app(MorphClassExists::class, ['implements' => Targetable::class]),
            ],
            'name' => 'required|string|max:255',
            'timeframe_column' => 'required|string',
            'aggregate_type' => 'required|string',
            'aggregate_column' => 'required|string',
            'owner_column' => 'required|string',
            'priority' => 'nullable|integer|min:0|max:255',
            'is_group_target' => 'boolean',

            'users' => 'nullable|array',
            'users.*.user_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'users.*.target_share' => [
                'nullable',
                app(Numeric::class),
            ],
            'users.*.is_percentage' => [
                'required_with:users.*.target_share',
                'exclude_without:users.*.target_share',
                'boolean',
            ],
        ];
    }
}
