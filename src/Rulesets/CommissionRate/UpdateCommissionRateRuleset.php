<?php

namespace FluxErp\Rulesets\CommissionRate;

use FluxErp\Models\CommissionRate;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class UpdateCommissionRateRuleset extends FluxRuleset
{
    protected static ?string $model = CommissionRate::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => CommissionRate::class]),
            ],
            'commission_rate' => 'required|numeric|lt:1|min:0',
        ];
    }
}
