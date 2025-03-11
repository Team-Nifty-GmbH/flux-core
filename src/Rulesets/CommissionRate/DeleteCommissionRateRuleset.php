<?php

namespace FluxErp\Rulesets\CommissionRate;

use FluxErp\Models\CommissionRate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteCommissionRateRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = CommissionRate::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => CommissionRate::class]),
            ],
        ];
    }
}
