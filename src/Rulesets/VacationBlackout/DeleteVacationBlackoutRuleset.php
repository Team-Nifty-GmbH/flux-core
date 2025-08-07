<?php

namespace FluxErp\Rulesets\VacationBlackout;

use FluxErp\Models\VacationBlackout;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rules\ModelExists;

class DeleteVacationBlackoutRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(VacationBlackout::class),
            ],
        ];
    }
}