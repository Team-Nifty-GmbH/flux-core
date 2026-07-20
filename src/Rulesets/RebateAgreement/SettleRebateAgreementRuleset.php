<?php

namespace FluxErp\Rulesets\RebateAgreement;

use FluxErp\Models\OrderType;
use FluxErp\Models\RebateAgreement;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class SettleRebateAgreementRuleset extends FluxRuleset
{
    protected static ?string $model = RebateAgreement::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => RebateAgreement::class]),
            ],
            'order_type_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => OrderType::class]),
            ],
        ];
    }
}
