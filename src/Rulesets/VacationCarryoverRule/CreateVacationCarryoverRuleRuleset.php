<?php

namespace FluxErp\Rulesets\VacationCarryoverRule;

use FluxErp\Rulesets\FluxRuleset;

class CreateVacationCarryoverRuleRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'max_days' => 'nullable|integer|min:0|max:365',
            'expires_after_months' => 'nullable|integer|min:1|max:60',
            'is_active' => 'boolean',
        ];
    }
}
