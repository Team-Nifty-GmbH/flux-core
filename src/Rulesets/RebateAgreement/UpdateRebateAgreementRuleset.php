<?php

namespace FluxErp\Rulesets\RebateAgreement;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\OrderType;
use FluxErp\Models\RebateAgreement;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class UpdateRebateAgreementRuleset extends FluxRuleset
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
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => OrderType::class])
                    ->where('order_type_enum', OrderTypeEnum::Refund)
                    ->where('is_active', true),
            ],
            'name' => 'nullable|string|max:255',
            'period_start' => 'required_with:period_end|date',
            'period_end' => 'required_with:period_start|date|after:period_start',
            'tiers' => 'sometimes|required|array|min:1',
            'tiers.*.from_volume' => [
                'required',
                'distinct',
                app(Numeric::class, ['min' => 0]),
            ],
            'tiers.*.percentage' => [
                'required',
                app(Numeric::class, ['min' => 0, 'max' => 1]),
            ],
            'is_active' => 'boolean',
        ];
    }
}
