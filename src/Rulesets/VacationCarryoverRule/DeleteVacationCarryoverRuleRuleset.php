<?php

namespace FluxErp\Rulesets\VacationCarryoverRule;

use FluxErp\Models\VacationCarryoverRule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteVacationCarryoverRuleRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => VacationCarryoverRule::class]),
            ],
        ];
    }
}
