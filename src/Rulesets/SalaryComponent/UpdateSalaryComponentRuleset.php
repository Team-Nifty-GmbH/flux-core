<?php

namespace FluxErp\Rulesets\SalaryComponent;

use FluxErp\Enums\PaymentIntervalEnum;
use FluxErp\Models\SalaryComponent;
use FluxErp\Rules\EnumRule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class UpdateSalaryComponentRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(SalaryComponent::class),
            ],
            'name' => 'sometimes|required|string|max:255',
            'amount' => [
                'sometimes',
                'required',
                app(Numeric::class),
            ],
            'payment_interval' => [
                'sometimes',
                'required',
                'string',
                app(EnumRule::class, ['type' => PaymentIntervalEnum::class]),
            ],
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
        ];
    }
}
