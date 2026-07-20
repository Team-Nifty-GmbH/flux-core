<?php

namespace FluxErp\Rulesets\RebateAgreement;

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
            'name' => 'nullable|string|max:255',
            'period_start' => 'sometimes|required|date',
            'period_end' => 'sometimes|required|date|after:period_start',
            'tiers' => 'sometimes|required|array|min:1',
            'tiers.*.from_volume' => ['required', 'distinct', app(Numeric::class, ['min' => 0])],
            'tiers.*.percentage' => ['required', app(Numeric::class, ['min' => 0, 'max' => 1])],
            'is_active' => 'sometimes|boolean',
        ];
    }
}
