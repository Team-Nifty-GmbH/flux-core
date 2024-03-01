<?php

namespace FluxErp\Rulesets\PaymentType;

use FluxErp\Models\PaymentType;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeletePaymentTypeRuleset extends FluxRuleset
{
    protected static ?string $model = PaymentType::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(PaymentType::class),
            ],
        ];
    }
}
