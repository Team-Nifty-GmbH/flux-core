<?php

namespace FluxErp\Rulesets\PaymentRun;

use FluxErp\Models\PaymentRun;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeletePaymentRunRuleset extends FluxRuleset
{
    protected static ?string $model = PaymentRun::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(PaymentRun::class),
            ],
        ];
    }
}
