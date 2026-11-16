<?php

namespace FluxErp\Rulesets\VacationCarryoverRule;

use FluxErp\Rulesets\FluxRuleset;

class CreateVacationCarryoverRuleRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'max_days' => 'nullable|numeric|min:0',
            'expires_at_month' => 'required_with:expires_at_day|nullable|integer|min:1|max:12',
            'expires_at_day' => 'required_with:expires_at_month|nullable|integer|min:1|max:31',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
