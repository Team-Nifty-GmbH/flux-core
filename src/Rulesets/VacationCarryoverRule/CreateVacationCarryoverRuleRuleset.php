<?php

namespace FluxErp\Rulesets\VacationCarryoverRule;

use FluxErp\Models\Client;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rules\ModelExists;

class CreateVacationCarryoverRuleRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'effective_year' => 'required|integer|min:2000|max:2100',
            'cutoff_month' => 'required|integer|min:1|max:12',
            'cutoff_day' => 'required|integer|min:1|max:31',
            'max_carryover_days' => 'nullable|integer|min:0|max:365',
            'expiry_date' => 'nullable|date|after:today',
            'is_active' => 'boolean',
            'client_id' => [
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
        ];
    }
}