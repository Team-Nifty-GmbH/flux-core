<?php

namespace FluxErp\Rulesets\Commission;

use FluxErp\Models\Commission;
use FluxErp\Models\CommissionRate;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateCommissionRuleset extends FluxRuleset
{
    protected static ?string $model = Commission::class;

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => User::class]),
            ],
            'commission_rate_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => CommissionRate::class]),
            ],
            'order_position_id' => [
                'integer',
                'nullable',
                app(ModelExists::class, ['model' => OrderPosition::class]),
            ],
            'commission_rate' => 'required_without:commission_rate_id|numeric|gt:0|lt:1',
            'total_net_price' => 'required_without:order_position_id|numeric',
        ];
    }
}
