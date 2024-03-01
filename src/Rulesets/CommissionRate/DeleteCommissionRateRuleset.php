<?php

namespace FluxErp\Rulesets\CommissionRate;

use FluxErp\Models\CommissionRate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteCommissionRateRuleset extends FluxRuleset
{
    protected static ?string $model = CommissionRate::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(CommissionRate::class),
            ],
        ];
    }
}
