<?php

namespace FluxErp\Rulesets\SalaryComponent;

use FluxErp\Enums\PaymentIntervalEnum;
use FluxErp\Models\Employee;
use FluxErp\Rules\EnumRule;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class CreateSalaryComponentRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'employee_id' => [
                'required',
                'integer',
                new ModelExists(Employee::class),
            ],
            'name' => 'required|string|max:255',
            'amount' => [
                'required',
                app(Numeric::class),
            ],
            'payment_interval' => [
                'required',
                'string',
                app(EnumRule::class, ['type' => PaymentIntervalEnum::class]),
            ],
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
        ];
    }
}
