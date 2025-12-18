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
            'expires_after_month' => 'required_with:expires_after_day|nullable|integer|min:1',
            'expires_after_day' => 'required_with:expires_after_month|nullable|integer|min:1|max:31',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
