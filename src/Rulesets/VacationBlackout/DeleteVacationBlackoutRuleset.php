<?php

namespace FluxErp\Rulesets\VacationBlackout;

use FluxErp\Models\VacationBlackout;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteVacationBlackoutRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => VacationBlackout::class]),
            ],
        ];
    }
}
