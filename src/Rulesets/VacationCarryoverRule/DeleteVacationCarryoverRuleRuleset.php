<?php

namespace FluxErp\Rulesets\VacationCarryoverRule;

use FluxErp\Models\VacationCarryoverRule;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rules\ModelExists;

class DeleteVacationCarryoverRuleRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(VacationCarryoverRule::class),
            ],
        ];
    }
}