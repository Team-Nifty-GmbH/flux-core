<?php

namespace FluxErp\Rulesets\VacationCarryoverRule;

use FluxErp\Models\VacationCarryOverRule;
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
                app(ModelExists::class, ['model' => VacationCarryOverRule::class]),
            ],
        ];
    }
}
