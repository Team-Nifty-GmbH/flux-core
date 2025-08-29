<?php

namespace FluxErp\Rulesets\VacationCarryoverRule;

use FluxErp\Models\Client;
use FluxErp\Models\VacationCarryoverRule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateVacationCarryoverRuleRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => VacationCarryoverRule::class]),
            ],
            'name' => 'sometimes|required|string|max:255',
            'max_days' => 'nullable|integer|min:0|max:365',
            'expires_after_months' => 'nullable|integer|min:1|max:60',
            'is_active' => 'boolean',
            'client_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => Client::class]),
            ],
        ];
    }
}
