<?php

namespace FluxErp\Rulesets\EmployeeBalanceAdjustment;

use FluxErp\Models\EmployeeBalanceAdjustment;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteEmployeeBalanceAdjustmentRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(EmployeeBalanceAdjustment::class),
            ],
        ];
    }
}
