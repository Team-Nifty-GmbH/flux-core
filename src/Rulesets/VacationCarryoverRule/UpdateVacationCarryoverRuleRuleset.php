<?php

namespace FluxErp\Rulesets\VacationCarryoverRule;

use FluxErp\Models\Client;
use FluxErp\Models\VacationCarryoverRule;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rules\ModelExists;

class UpdateVacationCarryoverRuleRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(VacationCarryoverRule::class),
            ],
            'effective_year' => 'sometimes|required|integer|min:2000|max:2100',
            'cutoff_month' => 'sometimes|required|integer|min:1|max:12',
            'cutoff_day' => 'sometimes|required|integer|min:1|max:31',
            'max_carryover_days' => 'nullable|integer|min:0|max:365',
            'expiry_date' => 'nullable|date|after:today',
            'is_active' => 'boolean',
            'client_id' => [
                'sometimes',
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
        ];
    }
}